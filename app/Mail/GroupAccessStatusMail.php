<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupAccessStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Registration $registration, public bool $suspended)
    {
    }

    public function envelope(): Envelope
    {
        $subjectKey = $this->suspended ? 'mail_group_suspended_subject' : 'mail_group_reactivated_subject';

        return new Envelope(subject: 'FULL MARK ACADEMY — '.__($subjectKey));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.group-access-status', with: [
            'registration' => $this->registration,
            'suspended' => $this->suspended,
        ]);
    }
}
