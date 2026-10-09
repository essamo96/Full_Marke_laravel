<?php

/*
 * Seeds throw-away data for tests/e2e/exam-offline.e2e.cjs (committed rows, because a real browser
 * talks to a real server). Everything is tagged and removed again by tests/e2e/cleanup.php.
 *
 *   php artisan tinker --execute="include 'tests/e2e/seed.php';"
 */

use App\Models\Exam;
use App\Models\Group;
use App\Models\Question;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

$tag = 'E2E'.date('His');
$password = 'E2e-pass-123';

$subject = Subject::factory()->create();
$teacher = Teacher::factory()->create();
$group = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'name' => "مجموعة $tag"]);

$exam = Exam::create([
    'subject_id' => $subject->id,
    'group_id' => $group->id,
    'title' => "امتحان محاكاة الانقطاع $tag",
    'status' => 'published',
    'audience' => 'students',
    'duration_minutes' => 30,
    'allow_student_review' => true,
]);
$exam->groups()->sync([$group->id]);

$questions = [];
for ($i = 1; $i <= 6; $i++) {
    $q = Question::create(['exam_id' => $exam->id, 'type' => 'multiple_choice', 'content' => "السؤال رقم $i", 'points' => 1, 'sort_order' => $i]);
    $options = [];
    foreach (['أ', 'ب', 'ج', 'د'] as $k => $label) {
        $options[] = $q->options()->create(['option_text' => "الخيار $label", 'is_correct' => $k === ($i % 4)])->id;
    }
    $questions[] = ['id' => $q->id, 'type' => 'mc', 'options' => $options, 'correct' => $options[$i % 4]];
}
$essay = Question::create(['exam_id' => $exam->id, 'type' => 'essay', 'content' => 'اشرح بأسلوبك', 'points' => 2, 'sort_order' => 7]);
$questions[] = ['id' => $essay->id, 'type' => 'essay', 'options' => [], 'correct' => null];

$students = [];
for ($n = 1; $n <= 12; $n++) {
    $student = Student::factory()->create([
        'email' => strtolower("e2e-$tag-$n@example.test"),
        'password' => $password,
        'email_verified_at' => now(),
        'status' => 1,
        'max_devices' => 3, // a scenario logs the same student in from a second "device"
    ]);
    Registration::factory()->create([
        'student_id' => $student->id,
        'group_id' => $group->id,
        'subject_id' => $subject->id,
        'status' => 'fully_paid',
    ]);
    $students[] = ['id' => $student->id, 'email' => $student->email];
}

$seed = [
    'tag' => $tag,
    'password' => $password,
    'exam_id' => $exam->id,
    'take_path' => route('student.exams.take', $exam, false),
    'subject_id' => $subject->id,
    'program_id' => $subject->program_id,
    'teacher_id' => $teacher->id,
    'group_id' => $group->id,
    'questions' => $questions,
    'students' => $students,
];

file_put_contents(storage_path('app/e2e-seed.json'), json_encode($seed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "seeded $tag: exam {$exam->id}, ".count($students)." students\n";
