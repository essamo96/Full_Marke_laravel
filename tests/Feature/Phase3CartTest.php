<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Group;
use App\Models\CartItem;
use App\Models\Registration;

class Phase3CartTest extends TestCase
{
    use DatabaseTransactions;

    public function test_verified_student_can_add_subject_to_cart()
    {
        $student = Student::factory()->create([
            'email_verified_at' => now(),
        ]);

        $subject = Subject::factory()->create();
        $group = Group::factory()->create([
            'subject_id' => $subject->id,
            'max_capacity' => 10,
            'current_count' => 0,
        ]);

        $response = $this->actingAs($student, 'student')
                         ->post(route('student.cart.store'), [
                             'subject_id' => $subject->id,
                             'group_id' => $group->id,
                         ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_cannot_add_duplicate_subject_if_already_registered()
    {
        $student = Student::factory()->create([
            'email_verified_at' => now(),
        ]);

        $subject = Subject::factory()->create();
        $group = Group::factory()->create([
            'subject_id' => $subject->id,
        ]);

        // Existing Registration
        Registration::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student, 'student')
                         ->post(route('student.cart.store'), [
                             'subject_id' => $subject->id,
                             'group_id' => $group->id,
                         ]);

        $response->assertSessionHasErrors('subject_id');
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $student->id,
            'user_type' => 'student',
        ]);
    }

    public function test_student_can_checkout_cart_items()
    {
        $student = Student::factory()->create([
            'email_verified_at' => now(),
        ]);

        $subject = Subject::factory()->create([
            'fee' => 100,
            'min_payment' => 50,
        ]);

        $group = Group::factory()->create([
            'subject_id' => $subject->id,
        ]);

        $cartItem = CartItem::create([
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $subject->id,
            'group_id' => $group->id,
        ]);

        $paymentMethod = \App\Models\PaymentMethod::factory()->create();

        // Create a dummy file for receipt
        \Illuminate\Support\Facades\Storage::fake('local');
        $file = \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($student, 'student')
                         ->post(route('student.checkout.store'), [
                             'amount' => 50, // min payment
                             'payment_method_id' => $paymentMethod->id,
                             'receipt' => $file,
                             'cart_item_ids' => [$cartItem->id],
                         ]);

        $response->assertRedirect(route('student.registrations'));
        $response->assertSessionHas('success');

        // Cart should be empty
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);

        // Registration created
        $this->assertDatabaseHas('registrations', [
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'fee_snapshot' => 100,
            'status' => 'pending',
        ]);

        // Payment created
        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'amount' => 50,
            'status' => 'pending',
        ]);
    }

    public function test_student_can_sync_cart()
    {
        $student = Student::factory()->create([
            'email_verified_at' => now(),
        ]);

        $subject1 = Subject::factory()->create();
        $subject2 = Subject::factory()->create();
        $group1 = Group::factory()->create(['subject_id' => $subject1->id]);

        // Pre-populate database cart to test it gets cleared
        CartItem::create([
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => Subject::factory()->create()->id,
        ]);

        $response = $this->actingAs($student, 'student')
                         ->postJson(route('student.cart.sync'), [
                             'items' => [
                                 ['subject_id' => $subject1->id, 'group_id' => $group1->id],
                                 ['subject_id' => $subject2->id, 'group_id' => null]
                             ]
                         ]);

        $response->assertJson([
            'status' => 'success',
            'redirect' => route('student.checkout'),
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $subject1->id,
            'group_id' => $group1->id,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $subject2->id,
            'group_id' => null,
        ]);

        // Old cart item should be deleted
        $this->assertEquals(2, CartItem::where('user_id', $student->id)->count());
    }

    public function test_checkout_dispatches_notification_to_admin()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $admin = \App\Models\Admin::create([
            'name' => 'Test Admin',
            'email' => 'test_admin_notif@test.com',
            'password' => bcrypt('password'),
            'status' => 1
        ]);

        $student = Student::factory()->create([
            'email_verified_at' => now(),
        ]);

        $subject = Subject::factory()->create([
            'fee' => 100,
            'min_payment' => 50,
        ]);

        $cartItem = CartItem::create([
            'user_id' => $student->id,
            'user_type' => 'student',
            'subject_id' => $subject->id,
        ]);

        $paymentMethod = \App\Models\PaymentMethod::factory()->create();

        $file = \Illuminate\Http\UploadedFile::fake()->image('receipt.jpg');

        $this->actingAs($student, 'student')
             ->post(route('student.checkout.store'), [
                 'amount' => 50,
                 'payment_method_id' => $paymentMethod->id,
                 'receipt' => $file,
                 'cart_item_ids' => [$cartItem->id],
             ]);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $admin,
            \App\Notifications\NewPaymentSubmittedNotification::class
        );
    }
}
