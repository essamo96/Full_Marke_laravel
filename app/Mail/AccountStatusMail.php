<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Student $student, public bool $suspended, public float $totalDue = 0)
    {
    }

    public function envelope(): Envelope
    {
        $subjectKey = $this->suspended ? 'mail_account_suspended_subject' : 'mail_account_reactivated_subject';

        return new Envelope(subject: 'FULL MARK ACADEMY — '.__($subjectKey));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-status', with: [
            'student' => $this->student,
            'suspended' => $this->suspended,
            'totalDue' => $this->totalDue,
        ]);
    }
}
