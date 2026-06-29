<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'FULL MARK ACADEMY — '.__('app.mail_payment_received_subject', ['number' => $this->payment->payment_number]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-received', with: ['payment' => $this->payment]);
    }
}
