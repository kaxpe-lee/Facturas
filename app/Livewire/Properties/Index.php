<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use App\Models\Client;
use App\Livewire\Forms\PropertyForm;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Inmuebles')]
class Index extends Component
{
    use WithPagination;

    public PropertyForm $form;
    public bool $editing = false;
    public string $search = '';

    public function create()
    {
        $this->form->resetForm();
        $this->editing = false;
    }

    public function edit(Property $property)
    {
        $this->form->setProperty($property);
        $this->editing = true;
    }

    public function delete(Property $property)
    {
        $property->delete();
    }

    public function save()
    {
        $this->form->store();
        $this->dispatch('close-modal-property-form');
    }

    public function render()
    {
        return view('livewire.properties.index', [
            'properties' => Property::with('tenant')
                ->where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
            'tenants' => Client::where('type', 'tenant')->orderBy('name')->get()
        ]);
    }
}
