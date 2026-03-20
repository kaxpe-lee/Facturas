<?php

namespace App\Livewire\Forms;

use App\Models\Client;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ClientForm extends Form
{
    public ?Client $client = null;

    #[Validate('required|min:3')]
    public $name = '';

    #[Validate('nullable|email')]
    public $email = '';

    #[Validate('nullable|string')]
    public $phone = '';

    #[Validate('nullable|string')]
    public $document_number = '';

    #[Validate('nullable|string')]
    public $billing_address = '';

    #[Validate('required|in:tenant,occasional')]
    public $type = 'tenant';

    public function setClient(Client $client)
    {
        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->document_number = $client->document_number;
        $this->billing_address = $client->billing_address;
        $this->type = $client->type;
    }

    public function resetForm() {
        $this->client = null;
        $this->reset(['name', 'email', 'phone', 'document_number', 'billing_address', 'type']);
        $this->type = 'tenant';
    }

    public function store()
    {
        $this->validate();

        if ($this->client) {
            $this->client->update($this->only(['name', 'email', 'phone', 'document_number', 'billing_address', 'type']));
        } else {
            Client::create($this->only(['name', 'email', 'phone', 'document_number', 'billing_address', 'type']));
        }

        $this->resetForm();
    }
}
