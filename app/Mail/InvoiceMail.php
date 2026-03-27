<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $settings;
    public $body;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, $body = null)
    {
        $this->invoice = $invoice;
        $this->body = $body;
        // Carga los ajustes predeterminados para pasarlos directo a la vista del PDF PDF
        $this->settings = Setting::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . str_replace('/', '-', $this->invoice->invoice_number) . ' - ' . ($this->settings['company_name'] ?? 'Tu Empresa'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Genera el PDF en memoria al momento de enviarlo para adjuntarlo en binario
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $this->invoice,
            'settings' => $this->settings
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'Factura_' . str_replace('/', '_', $this->invoice->invoice_number) . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
