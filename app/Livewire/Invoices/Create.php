<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Property;
use App\Models\Invoice;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Nueva Factura')]
class Create extends Component
{
    public $client_id = '';
    public $property_id = '';
    
    public $issue_date;
    public $due_date;
    
    public $iva_percentage = 0;
    public $retention_percentage = 0;
    
    public $notes = '';
    
    public $items = [];

    public function mount()
    {
        $this->issue_date = Carbon::now()->format('Y-m-d');
        $this->addItem();
    }

    public function updatedClientId($value)
    {
        $this->property_id = '';
    }

    public function updatedPropertyId($value)
    {
        if ($value) {
            $prop = Property::find($value);
            if ($prop) {
                if ($prop->default_iva_percentage > 0) $this->iva_percentage = (float) $prop->default_iva_percentage;
                if ($prop->default_retention_percentage > 0) $this->retention_percentage = (float) $prop->default_retention_percentage;
                
                if ($prop->monthly_rent > 0) {
                    $this->items[0] = [
                        'description' => 'Alquiler ' . $prop->name . ' - ' . Carbon::parse($this->issue_date)->translatedFormat('F Y'),
                        'quantity' => 1,
                        'unit_price' => (float) $prop->monthly_rent
                    ];
                }
                
                if (!$this->client_id && $prop->current_tenant_id) {
                    $this->client_id = $prop->current_tenant_id;
                }
            }
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0
        ];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    #[Computed]
    public function subtotal()
    {
        $sub = 0;
        foreach($this->items as $item) {
            $sub += ((float) $item['quantity'] * (float) $item['unit_price']);
        }
        return $sub;
    }

    #[Computed]
    public function ivaAmount()
    {
        return $this->subtotal() * ((float) $this->iva_percentage / 100);
    }

    #[Computed]
    public function retentionAmount()
    {
        return $this->subtotal() * ((float) $this->retention_percentage / 100);
    }

    #[Computed]
    public function totalAmount()
    {
        return $this->subtotal() + $this->ivaAmount() - $this->retentionAmount();
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'items.*.description.required' => 'La descripción es obligatoria en todas las líneas.',
            'client_id.required' => 'Debes seleccionar un cliente.',
            'items.*.quantity.required' => 'Requerido.'
        ]);

        $year = Carbon::parse($this->issue_date)->year;
        $count = Invoice::whereYear('issue_date', $year)->count() + 1;
        $number = $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'invoice_number' => $number,
            'client_id' => $this->client_id,
            'property_id' => $this->property_id ?: null,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date ?: null,
            'subtotal' => $this->subtotal(),
            'iva_percentage' => $this->iva_percentage ?: 0,
            'iva_amount' => $this->ivaAmount(),
            'retention_percentage' => $this->retention_percentage ?: 0,
            'retention_amount' => $this->retentionAmount(),
            'total_amount' => $this->totalAmount(),
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'total' => ((float) $item['quantity'] * (float) $item['unit_price']),
            ]);
        }

        return redirect()->route('invoices');
    }

    public function render()
    {
        return view('livewire.invoices.create', [
            'clients' => Client::orderBy('name')->get(),
            'properties' => Property::orderBy('name')->get()
        ]);
    }
}
