<?php

namespace App\Livewire\Forms;

use App\Models\Property;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PropertyForm extends Form
{
    public ?Property $property = null;

    #[Validate('required|min:2')]
    public $name = '';

    #[Validate('nullable|string|max:400')]
    public $description = '';

    #[Validate('nullable|string')]
    public $address = '';

    #[Validate('required|numeric|min:0')]
    public $monthly_rent = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $default_iva_percentage = 0;

    #[Validate('required|numeric|min:0|max:100')]
    public $default_retention_percentage = 0;

    #[Validate('boolean')]
    public $is_active = true;

    #[Validate('nullable|exists:clients,id')]
    public $current_tenant_id = null;

    public function setProperty(Property $property)
    {
        $this->property = $property;
        $this->name = $property->name;
        $this->description = $property->description;
        $this->address = $property->address;
        $this->monthly_rent = $property->monthly_rent;
        $this->default_iva_percentage = $property->default_iva_percentage;
        $this->default_retention_percentage = $property->default_retention_percentage;
        $this->is_active = $property->is_active;
        $this->current_tenant_id = $property->current_tenant_id;
    }

    public function resetForm() {
        $this->property = null;
        $this->reset(['name', 'description', 'address', 'monthly_rent', 'default_iva_percentage', 'default_retention_percentage', 'is_active', 'current_tenant_id']);
        $this->monthly_rent = 0;
        $this->default_iva_percentage = 0;
        $this->default_retention_percentage = 0;
        $this->is_active = true;
    }

    public function store()
    {
        $this->validate();

        $data = $this->only([
            'name', 'description', 'address', 'monthly_rent',
            'default_iva_percentage', 'default_retention_percentage',
            'is_active', 'current_tenant_id'
        ]);

        if ($this->property) {
            $this->property->update($data);
        } else {
            Property::create($data);
        }

        $this->resetForm();
    }
}
