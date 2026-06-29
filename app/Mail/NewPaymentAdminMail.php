<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPaymentAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New payment pending review — '.$this->payment->payment_number);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-payment-admin', with: ['payment' => $this->payment]);
    }
}
