<?php

namespace App\Livewire\Forms;

use App\Models\Property;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PropertyForm extends Form
{
    public ?Property $property = null;

    #[Validate('required|string|min:2|max:255')]
    public $name = '';

    #[Validate('nullable|string|max:400')]
    public $description = '';

    #[Validate('nullable|string|max:1000')]
    public $address = '';

    #[Validate('required|numeric|min:0')]
    public $monthly_rent = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $default_iva_percentage = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $default_retention_percentage = 0;

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('boolean')]
    public $include_in_taxes = true;

    #[Validate('nullable|exists:clients,id')]
    public $current_tenant_id = null;

    public function setProperty(Property $property): void
    {
        $this->resetValidation();
        $this->property = $property;
        $this->name = $property->name;
        $this->description = $property->description ?? '';
        $this->address = $property->address ?? '';
        $this->monthly_rent = $property->monthly_rent;
        $this->default_iva_percentage = $property->default_iva_percentage;
        $this->default_retention_percentage = $property->default_retention_percentage;
        $this->is_active = (bool) $property->is_active;
        $this->include_in_taxes = (bool) ($property->include_in_taxes ?? true);
        $this->current_tenant_id = $property->current_tenant_id ?? '';
    }

    public function resetForm(): void
    {
        $this->resetValidation();
        $this->property = null;
        $this->reset([
            'name',
            'description',
            'address',
            'monthly_rent',
            'default_iva_percentage',
            'default_retention_percentage',
            'is_active',
            'include_in_taxes',
            'current_tenant_id',
        ]);
        $this->monthly_rent = 0;
        $this->default_iva_percentage = 0;
        $this->default_retention_percentage = 0;
        $this->is_active = true;
        $this->include_in_taxes = true;
        $this->current_tenant_id = null;
    }

    public function store(): bool
    {
        $this->current_tenant_id = trim((string) $this->current_tenant_id) ?: null;
        $this->description = trim((string) $this->description) ?: null;
        $this->address = trim((string) $this->address) ?: null;

        $this->validate();

        $data = [
            'name' => trim($this->name),
            'description' => $this->description,
            'address' => $this->address,
            'monthly_rent' => (float) $this->monthly_rent,
            'default_iva_percentage' => (float) $this->default_iva_percentage,
            'default_retention_percentage' => (float) $this->default_retention_percentage,
            'is_active' => (bool) $this->is_active,
            'include_in_taxes' => (bool) $this->include_in_taxes,
            'current_tenant_id' => $this->current_tenant_id,
        ];

        $isUpdating = (bool) $this->property;

        if ($isUpdating) {
            $this->property->update($data);
        } else {
            Property::create($data);
        }

        $this->resetForm();

        return $isUpdating;
    }
}
