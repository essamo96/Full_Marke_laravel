<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Program;
use App\Models\StudyBranch;
use App\Models\Student;
use App\Models\News;
use App\Models\Contact;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\RateLimiter;
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

        // Generate a random 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        
        session([
            'pending_application_id' => $application->id,
            'pending_application_otp' => $otp,
        ]);

        // Send OTP email to student
        \Illuminate\Support\Facades\Mail::to($application->email)->send(new \App\Mail\VerificationCodeMail($otp));

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

        $expectedOtp = session('pending_application_otp');
        $pendingId = session('pending_application_id');

        if (!$pendingId || !$expectedOtp) {
            return redirect()->route('apply.create')->withErrors(['otp' => app()->getLocale() === 'ar' ? 'انتهت الصلاحية. الرجاء التقديم مجدداً.' : 'Session expired. Please apply again.']);
        }

        if ($request->input('otp') !== $expectedOtp) {
            return redirect()
                ->route('apply.create')
                ->with('applied', true)
                ->withErrors(['otp' => app()->getLocale() === 'ar' ? 'رمز التحقق غير صحيح!' : 'Invalid verification code!']);
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

        session()->forget(['pending_application_id', 'pending_application_otp']);

        auth('student')->login($student);

        return redirect()
            ->route('site.home')
            ->with('welcome_student', app()->getLocale() === 'ar' ? $student->full_name_ar : $student->full_name_en);
    }

    public function storeContact(Request $request)
    {
        // 1. Spam Prevention (Honeypot)
        if ($request->filled('website_url')) {
            return response()->json(['success' => false, 'message' => 'Spam detected.']);
        }

        // 2. Session Limit: Check if they already sent a message in this session
        if ($request->session()->has('contact_sent_time')) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' 
                    ? 'لقد قمت بإرسال رسالة مسبقاً في هذه الجلسة. شكراً لك.' 
                    : 'You have already sent a message in this session. Thank you.'
            ]);
        }

        // 3. Rate Limiting: 1 message per 2 hours (7200 seconds) per IP
        $executed = RateLimiter::attempt(
            'contact-form:' . $request->ip(),
            1,
            function() use ($request) {
                $data = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'nullable|string|max:30',
                    'subject' => 'required|string|max:255',
                    'message' => 'required|string',
                ]);

                $data['is_read'] = 0;
                $contact = Contact::create($data);

                $request->session()->put('contact_sent_time', now());

                // Optional: Pusher event for Admin Notification
                try {
                    event(new \App\Events\MyEvent([
                        'message' => 'New contact message from: ' . $contact->name,
                        'name' => $contact->name,
                        'branch' => 'Contact Us',
                        'study_branch' => $contact->subject,
                        'created_at' => now()->diffForHumans(),
                    ]));
                } catch (\Exception $e) {}
            },
            7200 // 2 hours
        );

        if (! $executed) {
            $seconds = RateLimiter::availableIn('contact-form:' . $request->ip());
            $hours = ceil($seconds / 3600);
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' 
                    ? "عذراً، يرجى الانتظار لمدة {$hours} ساعة/ساعات قبل إرسال رسالة أخرى." 
                    : "Please wait {$hours} hour(s) before sending another message."
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' 
                ? 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً!' 
                : 'Your message has been sent successfully. We will contact you soon!'
        ]);
    }

    public function newsDetails($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        $news = News::where('status', 1)->with('translations')->findOrFail($decryptedId);
        
        $similarNews = News::where('status', 1)
            ->where('id', '!=', $decryptedId)
            ->with('translations')
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        $page_title = $news->translation ? $news->translation->title : __('app.news');
        $page_description = $news->translation ? strip_tags($news->translation->description) : '';

        return view('site.news.show', compact('news', 'similarNews', 'page_title', 'page_description'));
    }
}
