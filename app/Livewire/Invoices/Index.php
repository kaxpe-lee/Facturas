<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Facturas')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

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
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('invoice'));
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Factura-' . str_replace('/', '-', $invoice->invoice_number) . '.pdf');
    }

    public function render()
    {
        return view('livewire.invoices.index', [
            'invoices' => Invoice::with(['client', 'property'])
                ->where('invoice_number', 'like', '%' . $this->search . '%')
                ->orWhereHas('client', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orderBy('issue_date', 'desc')
                ->paginate(10)
        ]);
    }
}
