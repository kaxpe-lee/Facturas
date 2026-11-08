<?php

namespace App\Livewire\Forms;

use App\Models\Invoice;
use App\Models\Property;
use Carbon\Carbon;
use Livewire\Attributes\Validate;
use Livewire\Form;

class InvoiceForm extends Form
{
    #[Validate('required|exists:properties,id')]
    public $property_id = '';

    #[Validate('required|exists:clients,id')]
    public $client_id = '';

    #[Validate('required|date')]
    public $issue_date = '';

    #[Validate('required|string|max:255')]
    public $description = 'Alquiler';

    #[Validate('required|numeric|min:0')]
    public $subtotal = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $iva_percentage = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $retention_percentage = 0;

    #[Validate('boolean')]
    public bool $include_in_taxes = true;

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->reset([
            'property_id',
            'client_id',
            'description',
            'subtotal',
            'iva_percentage',
            'retention_percentage',
            'include_in_taxes',
            'notes',
        ]);
        $this->issue_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->description = 'Alquiler';
        $this->subtotal = 0;
        $this->iva_percentage = 0;
        $this->retention_percentage = 0;
        $this->include_in_taxes = true;
    }

    public function loadFromProperty(?string $propertyId): void
    {
        $this->resetValidation(['property_id', 'client_id']);

        if (!$propertyId) {
            $this->client_id = '';
            $this->subtotal = 0;
            $this->iva_percentage = 0;
            $this->retention_percentage = 0;
            $this->include_in_taxes = true;
            $this->description = 'Alquiler';
            return;
        }

        $prop = Property::with('tenant')->find($propertyId);
        if (!$prop) {
            return;
        }

        $this->client_id = $prop->current_tenant_id ?: '';
        $this->subtotal = (float) $prop->monthly_rent;
        $this->iva_percentage = (float) $prop->default_iva_percentage;
        $this->retention_percentage = (float) $prop->default_retention_percentage;
        $this->include_in_taxes = (bool) ($prop->include_in_taxes ?? true);

        $date = $this->issue_date ? Carbon::parse($this->issue_date) : Carbon::now();
        $this->description = 'Alquiler ' . $prop->name . ' - ' . $date->translatedFormat('F Y');

        if (!$prop->current_tenant_id) {
            $this->addError('property_id', 'Este inmueble no tiene un cliente asociado.');
        }
    }

    public function ivaAmount(): float
    {
        return (float) $this->subtotal * ((float) $this->iva_percentage / 100);
    }

    public function retentionAmount(): float
    {
        return (float) $this->subtotal * ((float) $this->retention_percentage / 100);
    }

    public function totalAmount(): float
    {
        return (float) $this->subtotal + $this->ivaAmount() - $this->retentionAmount();
    }

    public function store(): Invoice
    {
        $this->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'description' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'iva_percentage' => 'required|numeric|min:0|max:100',
            'retention_percentage' => 'required|numeric|min:0|max:100',
            'include_in_taxes' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ], [
            'property_id.required' => 'Debes seleccionar un inmueble.',
            'client_id.required' => 'El inmueble seleccionado no tiene un cliente asociado.',
            'description.required' => 'El concepto es obligatorio.',
        ]);

        $number = Invoice::generateNextNumber($this->issue_date);
        $sub = (float) $this->subtotal;

        $invoice = Invoice::create([
            'invoice_number' => $number,
            'client_id' => $this->client_id,
            'property_id' => $this->property_id,
            'issue_date' => $this->issue_date,
            'subtotal' => $sub,
            'iva_percentage' => (float) $this->iva_percentage,
            'iva_amount' => $this->ivaAmount(),
            'retention_percentage' => (float) $this->retention_percentage,
            'retention_amount' => $this->retentionAmount(),
            'total_amount' => $this->totalAmount(),
            'notes' => trim((string) $this->notes) ?: null,
            'status' => 'pending',
            'include_in_taxes' => (bool) $this->include_in_taxes,
        ]);

        $invoice->items()->create([
            'description' => trim($this->description),
            'quantity' => 1,
            'unit_price' => $sub,
            'total' => $sub,
        ]);

        $this->resetForm();

        return $invoice;
    }
}

