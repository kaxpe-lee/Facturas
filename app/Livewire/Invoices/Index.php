<?php

namespace App\Livewire\Invoices;

use App\Livewire\Forms\InvoiceForm;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Property;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Facturas')]
class Index extends Component
{
    use WithPagination;

    public InvoiceForm $form;
    public string $search = '';

    public $selectedInvoiceId;
    public $emailTo = '';
    public $emailSubject = '';
    public $emailBody = '';
    public $attachmentName = '';

    public function mount(): void
    {
        $this->form->resetForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
    }

    public function updatedFormPropertyId($value): void
    {
        $this->form->loadFromProperty($value);
    }

    #[Computed]
    public function selectedClient(): ?Client
    {
        return $this->form->client_id ? Client::find($this->form->client_id) : null;
    }

    public function save(): void
    {
        $this->form->store();

        Flux::modal('invoice-form')->close();
        $this->dispatch('close-modal-invoice-form');

        Flux::toast('Factura creada correctamente.', variant: 'success');
    }

    public function delete(Invoice $invoice): void
    {
        $invoice->delete();
        Flux::toast('Factura eliminada correctamente.', variant: 'success');
    }

    public function markAsPaid(Invoice $invoice): void
    {
        $invoice->update(['status' => 'paid']);
        Flux::toast('Factura marcada como pagada.', variant: 'success');
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

    public function sendEmail(Invoice $invoice): void
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

        $this->emailBody = "Adjuntamos la factura " . $invoice->invoice_number . " por un importe de €" . number_format($invoice->total_amount, 2) . ".\n\nPor favor, revise el documento PDF adjunto.";

        Flux::modal('preview-email')->show();
    }

    public function confirmSend(): void
    {
        if (!$this->selectedInvoiceId) return;

        $this->validate([
            'emailTo' => 'required|email',
            'emailSubject' => 'required|string|max:255',
            'emailBody' => 'required|string',
        ]);

        $invoice = Invoice::with(['client', 'property', 'items'])->findOrFail($this->selectedInvoiceId);

        try {
            Mail::to($this->emailTo)->send(new InvoiceMail($invoice, $this->emailBody, $this->emailSubject));

            Flux::modal('preview-email')->close();

            $sentTo = $this->emailTo;
            $this->reset(['selectedInvoiceId', 'emailTo', 'emailSubject', 'emailBody', 'attachmentName']);

            Flux::toast('Factura enviada correctamente a ' . $sentTo, variant: 'success');
        } catch (\Throwable $e) {
            Flux::toast('Error enviando correo: ' . $e->getMessage(), variant: 'danger');
        }
    }

    public function render()
    {
        $searchTerm = '%' . mb_strtolower(trim($this->search)) . '%';

        return view('livewire.invoices.index', [
            'invoices' => Invoice::with(['client', 'property'])
                ->when(trim($this->search) !== '', function (Builder $query) use ($searchTerm) {
                    $query->where(function (Builder $q) use ($searchTerm) {
                        $q->whereRaw('LOWER(invoice_number) LIKE ?', [$searchTerm])
                            ->orWhereHas('client', function (Builder $cq) use ($searchTerm) {
                                $cq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                            })
                            ->orWhereHas('property', function (Builder $pq) use ($searchTerm) {
                                $pq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                            });
                    });
                })
                ->orderBy('issue_date', 'desc')
                ->paginate(10),
            'properties' => Property::with('tenant')->active()->orderBy('name')->get(),
        ]);
    }
}
