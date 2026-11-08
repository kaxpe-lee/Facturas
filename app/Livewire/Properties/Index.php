<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use App\Models\Client;
use App\Livewire\Forms\PropertyForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->form->resetForm();
        $this->editing = false;
    }

    public function edit(Property $property): void
    {
        $this->form->setProperty($property);
        $this->editing = true;
    }

    public function delete(Property $property): void
    {
        if ($property->invoices()->exists()) {
            Flux::toast(
                'No se puede eliminar este inmueble porque tiene facturas o albaranes asociados. Puedes marcarlo como inactivo.',
                variant: 'danger'
            );
            return;
        }

        $property->delete();

        Flux::toast('Inmueble eliminado correctamente.', variant: 'success');
    }

    public function save(): void
    {
        $wasEditing = $this->form->store();

        Flux::modal('property-form')->close();
        $this->dispatch('close-modal-property-form');

        Flux::toast(
            $wasEditing ? 'Inmueble actualizado correctamente.' : 'Inmueble creado correctamente.',
            variant: 'success'
        );
    }

    public function render()
    {
        $searchTerm = '%' . mb_strtolower(trim($this->search)) . '%';

        return view('livewire.properties.index', [
            'properties' => Property::with('tenant')
                ->withCount('invoices')
                ->when(trim($this->search) !== '', function (Builder $query) use ($searchTerm) {
                    $query->where(function (Builder $q) use ($searchTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(COALESCE(description, \'\')) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(COALESCE(address, \'\')) LIKE ?', [$searchTerm])
                            ->orWhereHas('tenant', function (Builder $tq) use ($searchTerm) {
                                $tq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]);
                            });
                    });
                })
                ->latest()
                ->paginate(10),
            'tenants' => Client::orderBy('name')->get(),
        ]);
    }
}
