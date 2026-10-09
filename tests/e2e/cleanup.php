<?php

/*
 * Removes everything tests/e2e/seed.php created (reads storage/app/e2e-seed.json).
 *
 *   php artisan tinker --execute="include 'tests/e2e/cleanup.php';"
 */

use Illuminate\Support\Facades\DB;

$path = storage_path('app/e2e-seed.json');
if (! is_file($path)) {
    echo "nothing to clean\n";

    return;
}

$seed = json_decode(file_get_contents($path), true);
$studentIds = array_column($seed['students'], 'id');
$examId = $seed['exam_id'];

DB::transaction(function () use ($seed, $studentIds, $examId) {
    DB::table('exam_answers')->where('exam_id', $examId)->delete();
    DB::table('grades')->where('exam_id', $examId)->delete();
    DB::table('exam_attempts')->where('exam_id', $examId)->delete();
    DB::table('student_exam_grants')->where('exam_id', $examId)->delete();
    DB::table('exam_group')->where('exam_id', $examId)->delete();
    $questionIds = DB::table('questions')->where('exam_id', $examId)->pluck('id');
    DB::table('question_options')->whereIn('question_id', $questionIds)->delete();
    DB::table('questions')->where('exam_id', $examId)->delete();
    DB::table('exams')->where('id', $examId)->delete();
    DB::table('notifications')->where('notifiable_type', \App\Models\Student::class)->whereIn('notifiable_id', $studentIds)->delete();
    DB::table('registrations')->whereIn('student_id', $studentIds)->delete();
    DB::table('students')->whereIn('id', $studentIds)->delete();
    DB::table('groups')->where('id', $seed['group_id'])->delete();
    DB::table('teachers')->where('id', $seed['teacher_id'])->delete();
    DB::table('subjects')->where('id', $seed['subject_id'])->delete();
    DB::table('programs')->where('id', $seed['program_id'])->delete();
});

unlink($path);
echo "cleaned {$seed['tag']}\n";
