<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Property;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Panel de control')]
class Dashboard extends Component
{
    #[Computed]
    public function clientStats(): array
    {
        return [
            'total' => Client::count(),
            'with_property' => Client::has('tenantedProperties')->count(),
        ];
    }

    #[Computed]
    public function propertyStats(): array
    {
        return [
            'total' => Property::count(),
            'active' => Property::active()->count(),
            'monthly_rent' => (float) Property::active()->sum('monthly_rent'),
        ];
    }

    #[Computed]
    public function invoiceStats(): array
    {
        $pending = Invoice::where('status', '!=', 'paid');

        return [
            'total' => Invoice::count(),
            'pending' => (clone $pending)->count(),
            'pending_amount' => (float) (clone $pending)->sum('total_amount'),
        ];
    }

    #[Computed]
    public function quarter(): array
    {
        $now = Carbon::now();
        $number = (int) ceil($now->month / 3);
        $start = Carbon::create($now->year, ($number - 1) * 3 + 1, 1)->startOfMonth();
        $end = (clone $start)->addMonths(2)->endOfMonth();

        return $this->summarize($start, $end) + [
            'label' => "{$number}T {$now->year}",
            'range' => $start->translatedFormat('M') . ' - ' . $end->translatedFormat('M'),
        ];
    }

    #[Computed]
    public function year(): array
    {
        $now = Carbon::now();

        return $this->summarize($now->copy()->startOfYear(), $now->copy()->endOfYear()) + [
            'label' => (string) $now->year,
            'range' => 'Ene - Dic',
        ];
    }

    private function summarize(Carbon $start, Carbon $end): array
    {
        $range = [$start->format('Y-m-d'), $end->format('Y-m-d')];

        $taxable = Invoice::taxable()->whereBetween('issue_date', $range);

        return [
            'base' => (float) (clone $taxable)->sum('subtotal'),
            'iva' => (float) (clone $taxable)->sum('iva_amount'),
            'retention' => (float) (clone $taxable)->sum('retention_amount'),
            'total' => (float) (clone $taxable)->sum('total_amount'),
            'count' => (clone $taxable)->count(),
            'excluded' => Invoice::where('include_in_taxes', false)->whereBetween('issue_date', $range)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
