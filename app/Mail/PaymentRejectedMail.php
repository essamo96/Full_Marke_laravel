<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'FULL MARK ACADEMY — '.__('app.mail_payment_rejected_subject'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-rejected', with: ['payment' => $this->payment]);
    }
}
