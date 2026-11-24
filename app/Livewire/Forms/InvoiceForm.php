<?php

namespace App\Livewire\Forms;

use App\Models\Invoice;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
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

    public function fillFromInvoice(Invoice $invoice): void
    {
        $this->resetValidation();
        $invoice->loadMissing('items');

        $this->property_id = $invoice->property_id ?? '';
        $this->client_id = $invoice->client_id;
        $this->issue_date = $invoice->issue_date->format('Y-m-d');
        $this->description = $invoice->items->first()?->description ?? 'Alquiler';
        $this->subtotal = (float) $invoice->subtotal;
        $this->iva_percentage = (float) $invoice->iva_percentage;
        $this->retention_percentage = (float) $invoice->retention_percentage;
        $this->include_in_taxes = (bool) $invoice->include_in_taxes;
        $this->notes = $invoice->notes ?? '';
    }

    public function update(Invoice $invoice): Invoice
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
        ]);

        $sub = (float) $this->subtotal;

        DB::transaction(function () use ($invoice, $sub) {
            // El número de factura se conserva siempre para no romper la correlación.
            $invoice->update([
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
                'include_in_taxes' => (bool) $this->include_in_taxes,
            ]);

            $invoice->items()->delete();
            $invoice->items()->create([
                'description' => trim($this->description),
                'quantity' => 1,
                'unit_price' => $sub,
                'total' => $sub,
            ]);
        });

        $this->resetForm();

        return $invoice;
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

        $sub = (float) $this->subtotal;

        // Reintenta si otra factura toma el mismo número a la vez.
        $attempts = 0;
        while (true) {
            try {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateNextNumber($this->issue_date),
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
                break;
            } catch (UniqueConstraintViolationException $e) {
                if (++$attempts >= 3) {
                    throw $e;
                }
            }
        }

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

