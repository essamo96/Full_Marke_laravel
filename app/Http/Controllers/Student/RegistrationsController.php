<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

    protected function authorizeOwnership(Registration $registration): void
    {
        $student = Auth::guard('student')->user();

        if ($registration->student_id !== $student->id) {
            abort(403);
        }
    }
}
