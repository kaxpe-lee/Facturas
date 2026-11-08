<?php

namespace App\Livewire\Forms;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClientForm extends Form
{
    public ?Client $client = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $document_number = '';
    public $billing_address = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'document_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('clients', 'document_number')->ignore($this->client?->id),
            ],
            'billing_address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function setClient(Client $client)
    {
        $this->resetValidation();
        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->document_number = $client->document_number ?? '';
        $this->billing_address = $client->billing_address ?? '';
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->client = null;
        $this->reset(['name', 'email', 'phone', 'document_number', 'billing_address']);
    }

    public function store(): bool
    {
        $this->document_number = trim((string) $this->document_number) ?: null;
        $this->email = trim((string) $this->email) ?: null;
        $this->phone = trim((string) $this->phone) ?: null;
        $this->billing_address = trim((string) $this->billing_address) ?: null;

        $this->validate();

        $data = [
            'name' => trim($this->name),
            'email' => $this->email,
            'phone' => $this->phone,
            'document_number' => $this->document_number,
            'billing_address' => $this->billing_address,
        ];

        $isUpdating = (bool) $this->client;

        if ($isUpdating) {
            $this->client->update($data);
        } else {
            Client::create($data);
        }

        $this->resetForm();

        return $isUpdating;
    }
}
