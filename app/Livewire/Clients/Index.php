<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Livewire\Forms\ClientForm;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Clientes e Inquilinos')]
class Index extends Component
{
    use WithPagination;

    public ClientForm $form;
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

    public function edit(Client $client): void
    {
        $this->form->setClient($client);
        $this->editing = true;
    }

    public function delete(Client $client): void
    {
        if ($client->invoices()->exists()) {
            Flux::toast(
                'No se puede eliminar este cliente porque tiene facturas o albaranes asociados.',
                variant: 'danger'
            );
            return;
        }

        $client->delete();

        Flux::toast('Cliente eliminado correctamente.', variant: 'success');
    }

    public function save(): void
    {
        $wasEditing = $this->form->store();

        Flux::modal('client-form')->close();
        $this->dispatch('close-modal-client-form');

        Flux::toast(
            $wasEditing ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.',
            variant: 'success'
        );
    }

    public function render()
    {
        $searchTerm = '%' . mb_strtolower(trim($this->search)) . '%';

        return view('livewire.clients.index', [
            'clients' => Client::query()
                ->with('tenantedProperties')
                ->withCount(['invoices', 'tenantedProperties'])
                ->when(trim($this->search) !== '', function (Builder $query) use ($searchTerm) {
                    $query->where(function (Builder $q) use ($searchTerm) {
                        $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(COALESCE(document_number, \'\')) LIKE ?', [$searchTerm])
                            ->orWhereRaw('LOWER(COALESCE(email, \'\')) LIKE ?', [$searchTerm]);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }
}
