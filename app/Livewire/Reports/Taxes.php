<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Impuestos Trimestrales')]
class Taxes extends Component
{
    public $year;
    public $quarter;

    public function mount()
    {
        $this->year = Carbon::now()->year;
        $this->quarter = ceil(Carbon::now()->month / 3);
    }

    #[Computed]
    public function reportData()
    {
        $startMonth = ($this->quarter - 1) * 3 + 1;
        $endMonth = $startMonth + 2;
        
        $start = Carbon::create($this->year, $startMonth, 1)->startOfMonth();
        $end = Carbon::create($this->year, $endMonth, 1)->endOfMonth();

        $invoices = Invoice::with(['client', 'property'])
            ->whereBetween('issue_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('issue_date', 'asc')
            ->get();

        return [
            'base' => $invoices->sum('subtotal'),
            'iva' => $invoices->sum('iva_amount'),
            'retention' => $invoices->sum('retention_amount'),
            'total' => $invoices->sum('total_amount'),
            'count' => $invoices->count(),
            'invoices' => $invoices
        ];
    }

    public function exportPdf()
    {
        $data = $this->reportData();
        $year = $this->year;
        $quarter = $this->quarter;
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.taxes', compact('data', 'year', 'quarter'));
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, "Resumen-Trimestre-{$quarter}T-{$year}.pdf");
    }

    public function render()
    {
        return view('livewire.reports.taxes');
    }
}
