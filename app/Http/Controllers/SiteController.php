<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Program;
use App\Models\StudyBranch;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function programDetails(Program $program): View
    {
        $program->load('subjects.groups');

        return view('site.program-details', compact('program'));
    }

    public function applyNow(Request $request): View
    {
        $programs = Program::where('is_active', true)->orderBy('order')->with('subjects')->get();
        $branches = Branch::active()->orderBy('name')->get();
        $studyBranches = StudyBranch::active()->orderBy('name_en')->get();
        $selectedProgram = $request->query('program');

        return view('site.apply-now', compact('programs', 'branches', 'studyBranches', 'selectedProgram'));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_ar' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'image' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'study_branch_id' => 'nullable|exists:study_branches,id',
            'major_profession' => 'nullable|string|max:255',
            'health_information' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('applications', 'public');
        }

        $application = Application::create($data);

        session(['pending_application_id' => $application->id]);

        // Trigger real-time Pusher event for the admin dashboard
        try {
            $msg = app()->getLocale() === 'ar'
                ? "طلب جديد من الطالب: " . $application->full_name_ar
                : "New application from student: " . $application->full_name_en;

            $eventData = [
                'message' => $msg,
                'name' => app()->getLocale() === 'ar' ? $application->full_name_ar : $application->full_name_en,
                'branch' => $application->branch?->name ?? '',
                'study_branch' => $application->studyBranch?->name ?? '',
                'created_at' => now()->diffForHumans(),
            ];

            event(new \App\Events\MyEvent($eventData));
        } catch (\Exception $e) {
            logger()->error("Pusher broadcast failed: " . $e->getMessage());
        }

        return redirect()
            ->route('apply.create')
            ->with('applied', true);
    }

    public function verifyApplication(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        if ($request->input('otp') !== '123456') {
            return redirect()
                ->route('apply.create')
                ->with('applied', true)
                ->withErrors(['otp' => app()->getLocale() === 'ar' ? 'رمز التحقق غير صحيح!' : 'Invalid verification code!']);
        }

        $pendingId = session('pending_application_id');
        if (!$pendingId) {
            return redirect()->route('apply.create')->withErrors(['otp' => app()->getLocale() === 'ar' ? 'انتهت الصلاحية. الرجاء التقديم مجدداً.' : 'Session expired. Please apply again.']);
        }

        $application = Application::find($pendingId);
        if (!$application) {
            return redirect()->route('apply.create')->withErrors(['otp' => app()->getLocale() === 'ar' ? 'الطلب غير موجود.' : 'Application not found.']);
        }

        // Check if student email already exists
        $student = Student::where('email', $application->email)->first();
        if (!$student) {
            $student = Student::create([
                'full_name_ar' => $application->full_name_ar,
                'full_name_en' => $application->full_name_en,
                'email' => $application->email,
                'phone' => $application->phone,
                'image' => $application->image,
                'date_of_birth' => $application->date_of_birth,
                'gender' => $application->gender,
                'address' => $application->address,
                'branch_id' => $application->branch_id,
                'study_branch_id' => $application->study_branch_id,
                'major_profession' => $application->major_profession,
                'health_information' => $application->health_information,
                'password' => bcrypt('password'),
                'status' => 1,
                'email_verified_at' => now(),
            ]);
        }

        session()->forget('pending_application_id');

        auth('student')->login($student);

        return redirect()
            ->route('site.home')
            ->with('welcome_student', app()->getLocale() === 'ar' ? $student->full_name_ar : $student->full_name_en);
    }
}
