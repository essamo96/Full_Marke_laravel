<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Registration;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegistrationsController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();

        $query = Registration::with(['subject.program', 'group'])
            ->where('student_id', $student->id)
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $registrations = $query->paginate(10)->withQueryString();

        return view('student.registrations.index', compact('registrations'));
    }

    public function show(Registration $registration)
    {
        $this->authorizeOwnership($registration);

        $registration->load(['subject.program', 'group', 'paymentItems.payment', 'subject.resources']);

        $paymentMethods = PaymentMethod::active()->orderBy('sort_order')->get();

        return view('student.registrations.show', compact('registration', 'paymentMethods'));
    }

    public function payRemaining(Request $request, Registration $registration, PaymentService $service)
    {
        $this->authorizeOwnership($registration);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'receipt' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        try {
            $service->payRemaining(
                $registration,
                (float) $data['amount'],
                (int) $data['payment_method_id'],
                $receiptPath,
                $data['notes'] ?? null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('student.registrations.show', $registration)->with('success', __('app.checkout_success'));
    }

    public function updateGroup(Request $request, Registration $registration)
    {
        $this->authorizeOwnership($registration);

        // Group selection stays optional after checkout too — a student can
        // pick one, switch, or clear it back to "no group" at any time.
        $request->validate(['group_id' => 'nullable|exists:groups,id']);

        if (! $request->filled('group_id')) {
            $registration->update(['group_id' => null]);

            return back()->with('success', __('app.update_success'));
        }

        $group = Group::findOrFail($request->group_id);

        if ($group->subject_id !== $registration->subject_id) {
            return back()->withErrors(['group_id' => __('app.not_found')]);
        }

        if (! $group->hasAvailableCapacity()) {
            return back()->withErrors(['group_id' => __('app.group_full')]);
        }

        $registration->update(['group_id' => $group->id]);

        return back()->with('success', __('app.update_success'));
    }

    protected function authorizeOwnership(Registration $registration): void
    {
        $student = Auth::guard('student')->user();

        if ($registration->student_id !== $student->id) {
            abort(403);
        }
    }
}
