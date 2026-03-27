<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Property;
use App\Models\Invoice;
use Livewire\Component;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Nueva Factura')]
class Create extends Component
{
    public $client_id = '';
    public $property_id = '';

    public $issue_date;
    public $due_date;

    public $iva_percentage = 0;
    public $retention_percentage = 0;

    public $notes = '';

    public $items = [];

    public $ai_prompt = '';
    public $ai_response = '';

    public function mount()
    {
        $this->issue_date = Carbon::now()->format('Y-m-d');
        $this->addItem();
    }

    public function updatedClientId($value)
    {
        $this->property_id = '';
    }

    public function updatedPropertyId($value)
    {
        if ($value) {
            $prop = Property::find($value);
            if ($prop) {
                if ($prop->default_iva_percentage > 0) $this->iva_percentage = (float) $prop->default_iva_percentage;
                if ($prop->default_retention_percentage > 0) $this->retention_percentage = (float) $prop->default_retention_percentage;

                if ($prop->monthly_rent > 0) {
                    $this->items[0] = [
                        'description' => 'Alquiler ' . $prop->name . ' - ' . Carbon::parse($this->issue_date)->translatedFormat('F Y'),
                        'quantity' => 1,
                        'unit_price' => (float) $prop->monthly_rent
                    ];
                }

                if (!$this->client_id && $prop->current_tenant_id) {
                    $this->client_id = $prop->current_tenant_id;
                }
            }
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => 'Alquiler',
            'quantity' => 1,
            'unit_price' => 0
        ];
    }

    public function removeItem($index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function generateFromAi()
    {
        if (empty($this->ai_prompt)) return;

        $properties = Property::all()->map(fn($p) => "ID: {$p->id}, Name: {$p->name}, TenantID: {$p->current_tenant_id}")->implode("\n");
        $clients = Client::all()->map(fn($c) => "ID: {$c->id}, Name: {$c->name}")->implode("\n");

        $prompt = "Analiza esta petición de factura: '{$this->ai_prompt}'.\n\nPropiedades disponibles:\n{$properties}\n\nClientes disponibles:\n{$clients}\n\nREGLA 1: Extrae los IDs. Si menciona un inmueble pero no un cliente explícito, usa el TenantID del inmueble para client_id.\nREGLA 2: Genera un mensaje_respuesta muy amigable, natural y conversacional (en español) en primera persona confirmando lo que has hecho. Por ejemplo: '¡Claro! He seleccionado el piso Centro y le he facturado el alquiler a Juan.' Si la petición no coincide con nadie, pide amablemente que especifique mejor el texto.";

        try {
            $response = Prism::structured()
                ->using(Provider::Gemini, 'gemini-2.5-flash')
                ->withPrompt($prompt)
                ->withSchema(new ObjectSchema(
                    name: 'extract_invoice_data',
                    description: 'Extracts the relevant property, client IDs and conversational response',
                    properties: [
                        new StringSchema('property_id', 'UUID of the matched property (leave empty string if not found)'),
                        new StringSchema('client_id', 'UUID of the matched client / tenant (leave empty string if not found)'),
                        new StringSchema('mensaje_respuesta', 'Tu respuesta amigable y de chat para el usuario notificandole lo que has hecho.')
                    ],
                    requiredFields: ['property_id', 'client_id', 'mensaje_respuesta']
                ))
                ->generate();

            $result = $response->structured;

            if (!empty($result['client_id'])) {
                $this->client_id = $result['client_id'];
            }
            if (!empty($result['property_id'])) {
                $this->property_id = $result['property_id'];
                $this->updatedPropertyId($this->property_id);
            }

            $this->ai_prompt = '';
            $this->ai_response = $result['mensaje_respuesta'] ?? '¡Hecho! Te he configurado los parámetros en la factura abajo.';

            Flux::toast('IA: ' . $this->ai_response);
        } catch (\Throwable $e) {
            $this->ai_response = 'Lo siento, ha habido un error en la conexión. (' . $e->getMessage() . ')';
        }
    }

    #[Computed]
    public function subtotal()
    {
        $sub = 0;
        foreach ($this->items as $item) {
            $sub += ((float) $item['quantity'] * (float) $item['unit_price']);
        }
        return $sub;
    }

    #[Computed]
    public function ivaAmount()
    {
        return $this->subtotal() * ((float) $this->iva_percentage / 100);
    }

    #[Computed]
    public function retentionAmount()
    {
        return $this->subtotal() * ((float) $this->retention_percentage / 100);
    }

    #[Computed]
    public function totalAmount()
    {
        return $this->subtotal() + $this->ivaAmount() - $this->retentionAmount();
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ], [
            'items.*.description.required' => 'La descripción es obligatoria en todas las líneas.',
            'client_id.required' => 'Debes seleccionar un cliente.',
            'items.*.quantity.required' => 'Requerido.'
        ]);

        $year = Carbon::parse($this->issue_date)->year;
        $count = Invoice::whereYear('issue_date', $year)->count() + 1;
        $number = $year . '/' . $count;

        $invoice = Invoice::create([
            'invoice_number' => $number,
            'client_id' => $this->client_id,
            'property_id' => $this->property_id ?: null,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date ?: null,
            'subtotal' => $this->subtotal(),
            'iva_percentage' => $this->iva_percentage ?: 0,
            'iva_amount' => $this->ivaAmount(),
            'retention_percentage' => $this->retention_percentage ?: 0,
            'retention_amount' => $this->retentionAmount(),
            'total_amount' => $this->totalAmount(),
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => (float) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'total' => ((float) $item['quantity'] * (float) $item['unit_price']),
            ]);
        }

        return redirect()->route('invoices');
    }

    public function render()
    {
        return view('livewire.invoices.create', [
            'clients' => Client::orderBy('name')->get(),
            'properties' => Property::orderBy('name')->get()
        ]);
    }
}
