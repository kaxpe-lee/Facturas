<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Livewire\Forms\ClientForm;
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

    public function create()
    {
        $this->form->resetForm();
        $this->editing = false;
    }

    public function edit(Client $client)
    {
        $this->form->setClient($client);
        $this->editing = true;
    }

    public function delete(Client $client)
    {
        $client->delete();
    }

    public function save()
    {
        $this->form->store();
        // Cierra el modal de Flux
        $this->dispatch('close-modal-client-form');
    }

    public function render()
    {
        return view('livewire.clients.index', [
            'clients' => Client::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('document_number', 'like', '%' . $this->search . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        ]);
    }
}
