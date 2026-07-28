<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Region;
use App\Models\Branch;
use App\Models\Program;
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
        // Route-model-binding resolves any program by slug regardless of status,
        // so an inactive program stayed reachable to anyone with (or guessing) its
        // URL even though it's hidden everywhere it'd normally be linked from.
        abort_unless($program->is_active, 404);

        $program->load(['subjects' => function ($query) {
            $query->active()->orderBy('sort_order');
        }, 'subjects.groups']);

        return view('site.program-details', compact('program'));
    }

    public function applyNow(Request $request): View
    {
        $programs = Program::where('is_active', true)->orderBy('sort_order')->with('subjects')->get();
        $regions = Region::where('status', 1)->get();
        $branches = Branch::where('status', 1)->get();
        
        $selectedProgram = null;
        if ($request->filled('program')) {
            $selectedProgram = Program::where('slug', $request->query('program'))->where('is_active', true)->first();
        }

        $selectedSubject = null;
        if ($request->filled('subject')) {
            try {
                $decryptedId = Crypt::decrypt($request->query('subject'));
                $selectedSubject = \App\Models\Subject::find($decryptedId);
            } catch (\Exception $e) {
                // Ignore if decryption fails
            }
        }

        return view('site.apply-now', compact('programs', 'regions', 'branches', 'selectedProgram', 'selectedSubject'));
    }

    public function storeApplication(Request $request, \App\Actions\SendVerificationCodeAction $sendCodeAction)
    {
        $data = $request->validate([
            'full_name_en' => 'required|string|max:255',
            'full_name_ar' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'phone' => 'required|string|max:30',
            'image' => 'nullable|image|max:2048',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'region_id' => 'nullable|exists:regions,id',
            'branch_id' => 'nullable|exists:branches,id',
            'major_profession' => 'nullable|string|max:255',
            'health_information' => 'nullable|string',
            'message' => 'nullable|string',
            'program_id' => 'nullable|exists:programs,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('applications', 'public');
        }

        // Create the Student first (unverified)
        $student = Student::create([
            'full_name_ar' => $data['full_name_ar'],
            'full_name_en' => $data['full_name_en'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'image' => $data['image'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'region_id' => $data['region_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'major_profession' => $data['major_profession'] ?? null,
            'health_information' => $data['health_information'] ?? null,
            'password' => bcrypt('password'), // Will be prompted to change later or handled by profile
            'status' => 1, // Active, but restricted by email_verified_at middleware
            'email_verified_at' => null,
        ]);

        $application = Application::create($data);

        // Send OTP via the new Action
        $sendCodeAction->execute($student);

        // Trigger real-time Pusher event for the admin dashboard
        try {
            \Illuminate\Support\Facades\Notification::send(
                \App\Models\Admin::all(), 
                new \App\Notifications\NewStudentRegisteredNotification($student)
            );
        } catch (\Exception $e) {
            logger()->error("Pusher broadcast failed: " . $e->getMessage());
        }


        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Application submitted successfully. Please verify your email.',
                'email' => $student->email
            ]);
        }

        return redirect()->back()->with('show_otp_modal', true)->with('email', $student->email);
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

                // Send Notification
                try {
                    \Illuminate\Support\Facades\Notification::send(
                        \App\Models\Admin::all(), 
                        new \App\Notifications\NewContactMessageNotification($contact)
                    );
                } catch (\Exception $e) {
                    logger()->error("Contact Us Notification failed: " . $e->getMessage());
                }
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
