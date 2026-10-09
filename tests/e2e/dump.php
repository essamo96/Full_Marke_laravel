<?php

/*
 * Prints (as one "E2E_DUMP:{json}" line) the grade + graded answers of every seeded student,
 * so exam-offline.e2e.cjs can compare what was GRADED with what the student clicked.
 */

use App\Models\ExamAnswer;
use App\Models\Grade;

$seed = json_decode(file_get_contents(storage_path('app/e2e-seed.json')), true);
$out = [];

foreach ($seed['students'] as $student) {
    $grades = Grade::where('exam_id', $seed['exam_id'])->where('student_id', $student['id'])->get();
    $grade = $grades->first();
    $out[$student['id']] = $grade ? [
        'grades' => $grades->count(),
        'score' => (float) $grade->score,
        'max' => (float) $grade->max_score,
        'notes' => $grade->notes,
        'auto_submitted' => (bool) $grade->auto_submitted,
        'tab_switch_count' => (int) $grade->tab_switch_count,
        'answers' => ExamAnswer::where('grade_id', $grade->id)->get(['question_id', 'selected_option_id', 'essay_answer'])->toArray(),
    ] : null;
}

echo 'E2E_DUMP:'.json_encode($out, JSON_UNESCAPED_UNICODE)."\n";
