<div>
    <div class="mb-6">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('invoices') }}">Facturas</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Nueva Factura</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        
        <flux:heading size="xl" class="mt-4">Crear Factura</flux:heading>
        <flux:subheading>Rellena los detalles para emitir una nueva factura.</flux:subheading>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="md:col-span-2 space-y-6">
                <!-- Detalles de Emisión -->
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Detalles Generales</flux:heading>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:select wire:model.live="client_id" label="Cliente *" required>
                            <option value="">Seleccione Cliente/Inquilino</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->document_number ?: 'Sin NIF' }})</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="property_id" label="Inmueble Vinculado (Opcional)">
                            <option value="">Ninguno</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->name }}</option>
                            @endforeach
                        </flux:select>
                        
                        <flux:input type="date" wire:model="issue_date" label="Fecha Emisión *" required />
                        <flux:input type="date" wire:model="due_date" label="Fecha Vencimiento" />
                    </div>
                </flux:card>

                <!-- Líneas de Factura -->
                <flux:card>
                    <flux:heading size="lg" class="mb-2">Conceptos</flux:heading>
                    
                    <div class="rounded-xl border border-zinc-200 shadow-sm overflow-hidden mb-4">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-zinc-50 text-zinc-600 font-medium">
                                <tr>
                                    <th class="px-4 py-2">Descripción</th>
                                    <th class="px-4 py-2 w-24">Cant.</th>
                                    <th class="px-4 py-2 w-32">Precio Und.</th>
                                    <th class="px-4 py-2 w-32">Total</th>
                                    <th class="px-4 py-2 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($items as $index => $item)
                                <tr class="bg-white">
                                    <td class="p-2">
                                        <flux:input wire:model.live="items.{{ $index }}.description" placeholder="Concepto..." required />
                                    </td>
                                    <td class="p-2">
                                        <flux:input type="number" step="0.01" wire:model.live="items.{{ $index }}.quantity" required />
                                    </td>
                                    <td class="p-2">
                                        <flux:input type="number" step="0.01" wire:model.live="items.{{ $index }}.unit_price" required />
                                    </td>
                                    <td class="p-2 pt-4 font-medium text-zinc-700">
                                        €{{ number_format((float)$items[$index]['quantity'] * (float)$items[$index]['unit_price'], 2) }}
                                    </td>
                                    <td class="p-2 pt-3">
                                        @if(count($items) > 1)
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="removeItem({{ $index }})" />
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <flux:button variant="subtle" size="sm" icon="plus" wire:click="addItem">Añadir Línea</flux:button>
                </flux:card>
                
                <flux:card>
                    <flux:textarea wire:model="notes" label="Notas adicionales (Opcional)" rows="3" />
                </flux:card>
            </div>

            <!-- Panel de Totales e Impuestos -->
            <div class="space-y-6">
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Impuestos y Resumen</flux:heading>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <flux:input type="number" step="0.01" wire:model.live="iva_percentage" label="IVA (%)" />
                        <flux:input type="number" step="0.01" wire:model.live="retention_percentage" label="IRPF (%)" />
                    </div>

                    <div class="space-y-3 pt-4 border-t border-zinc-200">
                        <div class="flex justify-between text-zinc-600">
                            <span>Base Imponible</span>
                            <span>€{{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        
                        @if($iva_percentage > 0)
                        <div class="flex justify-between text-zinc-600">
                            <span>IVA ({{ $iva_percentage }}%)</span>
                            <span>€{{ number_format($this->ivaAmount, 2) }}</span>
                        </div>
                        @endif
                        
                        @if($retention_percentage > 0)
                        <div class="flex justify-between text-zinc-600">
                            <span>Retención IRPF ({{ $retention_percentage }}%)</span>
                            <span class="text-red-500">-€{{ number_format($this->retentionAmount, 2) }}</span>
                        </div>
                        @endif
                        
                        <div class="flex justify-between text-lg font-bold text-zinc-900 pt-3 border-t border-zinc-200">
                            <span>Total Factura</span>
                            <span>€{{ number_format($this->totalAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-4">
                        <flux:button type="submit" variant="primary" class="w-full">Guardar Factura</flux:button>
                    </div>
                </flux:card>
            </div>
        </div>
    </form>
</div>
