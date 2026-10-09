<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ExamService
{
    /**
     * Create or update an exam with its questions and options.
     */
    public function saveExam(array $data, ?Exam $exam = null): Exam
    {
        return DB::transaction(function () use ($data, $exam) {
            $questions = $data['questions'] ?? null;
            unset($data['questions']);

            // group_ids is the full target list; exams.group_id keeps the first one as the primary group.
            $groupIds = $this->resolveGroupIds($data, $exam);
            unset($data['group_ids']);
            $data['group_id'] = $groupIds[0];

            $exclusionsPosted = array_key_exists('excluded_student_ids', $data) || ! empty($data['exclusions_present']);
            unset($data['exclusions_present']);
            if ($exclusionsPosted) {
                $data['excluded_student_ids'] = $this->normalizeIds($data['excluded_student_ids'] ?? []) ?: null;
            }

            if (array_key_exists('allow_student_review', $data)) {
                $data['allow_student_review'] = filter_var($data['allow_student_review'], FILTER_VALIDATE_BOOLEAN);
            }

            if ($exam) {
                $exam->update($data);
            } else {
                $exam = Exam::create($data);
            }

            $exam->groups()->sync($groupIds);
            $exam->unsetRelation('groups');

            if (is_array($questions)) {
                $this->syncQuestions($exam, $questions);
            }

            return $exam;
        });
    }

    /** @return array<int, int> non-empty list of target group ids */
    protected function resolveGroupIds(array $data, ?Exam $exam): array
    {
        $ids = $this->normalizeIds($data['group_ids'] ?? ($data['group_id'] ?? []));

        if ($ids === [] && $exam) {
            $ids = $exam->allGroupIds();
        }

        if ($ids === []) {
            throw new \InvalidArgumentException('An exam needs at least one target group.');
        }

        return $ids;
    }

    /** @return array<int, int> */
    protected function normalizeIds(mixed $ids): array
    {
        return collect(is_array($ids) ? $ids : [$ids])
            ->filter(fn ($id) => $id !== null && $id !== '' && is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Synchronize questions and their options.
     */
    protected function syncQuestions(Exam $exam, array $questionsData)
    {
        $existingQuestionIds = [];

        foreach ($questionsData as $index => $qData) {
            $questionId = (isset($qData['id']) && is_numeric($qData['id'])) ? $qData['id'] : null;

            $question = $exam->questions()->updateOrCreate(
                ['id' => $questionId],
                [
                    'type' => $qData['type'],
                    'content' => $qData['content'],
                    'points' => $qData['points'] ?? 1,
                    'sort_order' => $qData['sort_order'] ?? $index,
                ]
            );

            $existingQuestionIds[] = $question->id;

            if (isset($qData['options']) && in_array($qData['type'], ['true_false', 'multiple_choice'])) {
                $this->syncOptions($question, $qData['options']);
            } else {
                $question->options()->delete();
            }
        }

        // Delete removed questions
        $exam->questions()->whereNotIn('id', $existingQuestionIds)->delete();
    }

    /**
     * Synchronize question options.
     */
    protected function syncOptions($question, array $optionsData)
    {
        $existingOptionIds = [];

        foreach ($optionsData as $optData) {
            // For true_false or multiple_choice, make sure we have text
            if (empty($optData['option_text'])) {
                continue;
            }
            $optionId = (isset($optData['id']) && is_numeric($optData['id'])) ? $optData['id'] : null;

            $option = $question->options()->updateOrCreate(
                ['id' => $optionId],
                [
                    'option_text' => $optData['option_text'],
                    'is_correct' => isset($optData['is_correct']) ? (bool) $optData['is_correct'] : false,
                ]
            );

            $existingOptionIds[] = $option->id;
        }

        $question->options()->whereNotIn('id', $existingOptionIds)->delete();
    }

    /**
     * Calculate total points for an exam.
     */
    public function calculateTotalPoints(Exam $exam): int
    {
        return $exam->questions()->sum('points');
    }
}
