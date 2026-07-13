<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class Phase4FinancialTest extends TestCase
{
    use DatabaseTransactions;

    protected $program;
    protected $subject;
    protected $group;
    protected $student;
    protected $admin;
    protected $paymentMethod;
    protected $registration;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        // Setup base data
        $this->program = Program::factory()->create(['type' => 'general']);
        $this->subject = Subject::factory()->create(['program_id' => $this->program->id, 'fee' => 100, 'min_payment' => 50]);
        $this->group = Group::factory()->create(['subject_id' => $this->subject->id, 'max_capacity' => 10]);
        $this->student = Student::factory()->create();
        
        $this->admin = Admin::first();
        if (!$this->admin) {
            $this->admin = Admin::create([
                'name' => 'Admin',
                'email' => 'admin_test@test.com',
                'password' => bcrypt('password'),
                'status' => 1
            ]);
        }
        $this->admin->assignRole('Super Admin');
        
        $this->paymentMethod = \App\Models\PaymentMethod::create([
            'name_ar' => 'بنكي',
            'name_en' => 'Bank',
            'is_active' => 1,
        ]);
        
        // Seed some pending registrations
        $this->registration = Registration::create([
            'registration_number' => 'REG-001',
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'group_id' => $this->group->id,
            'fee_snapshot' => 100,
            'amount_paid' => 0,
            'status' => 'pending',
        ]);

        $this->payment = Payment::create([
            'payment_number' => 'PAY-001',
            'student_id' => $this->student->id,
            'method' => $this->paymentMethod->id,
            'amount' => 100,
            'status' => 'pending',
            'receipt_image' => 'dummy_receipt.jpg',
        ]);

        \App\Models\PaymentItem::create([
            'payment_id' => $this->payment->id,
            'registration_id' => $this->registration->id,
            'allocated_amount' => 100,
        ]);
    }

    public function test_admin_can_view_approvals_list()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('approvals.view'));
        $response->assertStatus(200);
        $response->assertSee('طلبات الدفع المعلقة');
        $response->assertSee('PAY-001');
    }

    public function test_admin_can_confirm_payment_and_job_is_dispatched()
    {
        Queue::fake([\App\Jobs\GenerateInvoiceJob::class]);
        Mail::fake();

        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('approvals.confirm'), [
            'id' => Crypt::encrypt($this->payment->id)
        ]);

        $response->assertRedirect(route('approvals.view'));
        $response->assertSessionHas('success');

        $this->payment->refresh();
        $this->assertEquals('confirmed', $this->payment->status);

        $this->registration->refresh();
        $this->assertEquals('fully_paid', $this->registration->status);
        $this->assertEquals(100, $this->registration->amount_paid);

        $this->group->refresh();
        $this->assertEquals(1, $this->group->current_count); // capacity incremented

        Queue::assertPushed(\App\Jobs\GenerateInvoiceJob::class);
        Mail::assertSent(\App\Mail\PaymentConfirmedMail::class);
    }

    public function test_admin_can_reject_payment()
    {
        Mail::fake();

        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('approvals.reject'), [
            'id' => Crypt::encrypt($this->payment->id),
            'rejection_reason' => 'Receipt is blurry'
        ]);

        $response->assertRedirect(route('approvals.view'));
        
        $this->payment->refresh();
        $this->assertEquals('rejected', $this->payment->status);
        $this->assertEquals('Receipt is blurry', $this->payment->rejection_reason);

        Mail::assertSent(\App\Mail\PaymentRejectedMail::class);
    }

    public function test_invoice_job_generates_pdf()
    {
        // Don't fake Queue here to run the job
        $job = new \App\Jobs\GenerateInvoiceJob($this->payment);
        $job->handle();

        $this->payment->refresh();
        $this->assertNotNull($this->payment->invoice_number);
        
        // Assert file exists
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists('private/invoices/invoice_' . $this->payment->invoice_number . '.pdf'));
    }
}
