<?php

namespace App\Jobs;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payment;

    /**
     * Create a new job instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate a sequence invoice number if it doesn't exist
        if (! $this->payment->invoice_number) {
            $this->payment->update([
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($this->payment->id, 5, '0', STR_PAD_LEFT)
            ]);
        }

        $payment = $this->payment->load(['student', 'paymentMethod', 'items.registration.subject']);
        
        $pdf = Pdf::loadView('admin.payments.invoice', ['payment' => $payment]);
        
        // Save the PDF file securely in storage/app/private/invoices
        $filename = 'invoice_' . $payment->invoice_number . '.pdf';
        Storage::disk('local')->put('private/invoices/' . $filename, $pdf->output());

        // We can save the path back to the payment if needed, 
        // but typically it's dynamically generated or fetched via route.
        // We'll assume the path is predictable or we can add an `invoice_path` column.
    }
}
