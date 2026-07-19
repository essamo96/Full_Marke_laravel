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
            if ($exam) {
                $exam->update($data);
            } else {
                $exam = Exam::create($data);
            }

            // Sync questions if provided
            if (isset($data['questions'])) {
                $this->syncQuestions($exam, $data['questions']);
            }

            return $exam;
        });
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
