<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Flux\Flux;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Facturas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public $selectedInvoiceId;
    public $emailTo = '';
    public $emailSubject = '';
    public $emailBody = '';
    public $attachmentName = '';

    public function delete(Invoice $invoice)
    {
        $invoice->delete();
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update(['status' => 'paid']);
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client', 'property', 'items']);
        $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice', 'settings'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Factura-' . str_replace('/', '-', $invoice->invoice_number) . '.pdf');
    }

    public function sendEmail(Invoice $invoice)
    {
        $invoice->load(['client', 'property', 'items']);

        if (empty($invoice->client->email)) {
            Flux::toast('El cliente ' . $invoice->client->name . ' no tiene un correo asignado en su ficha.', variant: 'danger');
            return;
        }

        $this->selectedInvoiceId = $invoice->id;
        $this->emailTo = $invoice->client->email;

        $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
        $companyName = $settings['company_name'] ?? 'Tu Empresa';

        $this->emailSubject = 'Factura ' . str_replace('/', '-', $invoice->invoice_number) . ' - ' . $companyName;
        $this->attachmentName = 'Factura_' . str_replace('/', '_', $invoice->invoice_number) . '.pdf';

        $this->emailBody = "Hola " . $invoice->client->name . ",\n\nAdjuntamos la factura " . $invoice->invoice_number . " por un importe de €" . number_format($invoice->total_amount, 2) . ".\n\nPor favor, revise el documento adjunto.";

        Flux::modal('preview-email')->show();
    }

    public function confirmSend()
    {
        if (!$this->selectedInvoiceId) return;

        $invoice = Invoice::findOrFail($this->selectedInvoiceId);

        try {
            Mail::to($this->emailTo)->send(new InvoiceMail($invoice, $this->emailBody));

            Flux::modal('preview-email')->close();

            $this->reset(['selectedInvoiceId', 'emailTo', 'emailSubject', 'emailBody', 'attachmentName']);

            Flux::toast('¡Simulado! Correo interno enviado a ' . $invoice->client->email, variant: 'success');
        } catch (\Throwable $e) {
            Flux::toast('Error enviando correo: ' . $e->getMessage(), variant: 'danger');
        }
    }

    public function render()
    {
        return view('livewire.invoices.index', [
            'invoices' => Invoice::with(['client', 'property'])
                ->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('client', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orderBy('issue_date', 'desc')
                ->paginate(10)
        ]);
    }
}
