<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;

class ExamsController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        
        // Get all groups the student is registered to
        $groupIds = $student->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid'])
            ->pluck('group_id');
            
        // Get exams for these groups
        $exams = Exam::whereIn('group_id', $groupIds)
            ->where('status', 'published')
            ->where(function($query) use ($student) {
                // Not in excluded array (using JSON contains workaround)
                $query->whereNull('excluded_student_ids')
                      ->orWhereJsonDoesntContain('excluded_student_ids', $student->id);
            })
            ->latest()
            ->get();
            
        return view('student.exams.index', compact('exams'));
    }

    public function take(Exam $exam)
    {
        // TODO: Validate if student can take this exam (not excluded, in time, didn't submit before)
        $exam->load('questions.options');
        
        $student = auth('student')->user();
        $cacheKey = 'exam_start_' . $student->id . '_' . $exam->id;
        
        if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, now(), now()->addDay());
        }

        return view('student.exams.take', compact('exam'));
    }

    public function submit(Request $request, Exam $exam)
    {
        // Ideally we should record this in an `exam_submissions` or `grades` table.
        // We will assume a simple Grade recording for now.
        $student = auth('student')->user();
        
        $exam->load('questions.options');
        
        $totalPoints = 0;
        $earnedPoints = 0;
        
        foreach ($exam->questions as $question) {
            $totalPoints += $question->points;
            
            $answerId = $request->input('answers.' . $question->id);
            
            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $correctOption = $question->options->where('is_correct', true)->first();
                if ($correctOption && $answerId == $correctOption->id) {
                    $earnedPoints += $question->points;
                }
            } else if ($question->type === 'essay') {
                // Essay needs manual grading later. We just record 0 for now or store it.
            }
        }
        
        $cacheKey = 'exam_start_' . $student->id . '_' . $exam->id;
        $startTime = \Illuminate\Support\Facades\Cache::get($cacheKey);
        $timeTaken = $startTime ? now()->diffInMinutes($startTime) : null;
        
        // Save the result to grades table
        \App\Models\Grade::create([
            'student_id' => $student->id,
            'group_id' => $exam->group_id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->title,
            'score' => $earnedPoints,
            'max_score' => $totalPoints,
            'notes' => 'تم التصحيح الآلي (باستثناء الأسئلة المقالية إن وجدت)',
            'started_at' => $startTime,
            'time_taken_minutes' => $timeTaken,
        ]);
        
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        return redirect()->route('student.exams.index')->with('success', "تم استلام امتحانك بنجاح. نتيجتك المبدئية: {$earnedPoints} من {$totalPoints}.");
    }
}
