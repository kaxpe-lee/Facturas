<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Registro de Facturas</flux:heading>
        
        <flux:modal.trigger name="invoice-form">
            <flux:button variant="primary" wire:click="create" icon="plus">Nueva Factura</flux:button>
        </flux:modal.trigger>
    </div>
    
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número, cliente o inmueble..." icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Número</flux:table.column>
            <flux:table.column>Fecha factura</flux:table.column>
            <flux:table.column>Cliente / Inmueble</flux:table.column>
            <flux:table.column>Totales</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($invoices as $invoice)
            <flux:table.row>
                <flux:table.cell class="font-medium whitespace-nowrap">{{ $invoice->invoice_number }}</flux:table.cell>
                <flux:table.cell>{{ $invoice->issue_date->format('d/m/Y') }}</flux:table.cell>
                <flux:table.cell>
                    <div class="font-medium">{{ $invoice->client->name }}</div>
                    @if($invoice->property)
                        <div class="text-sm text-zinc-500">{{ $invoice->property->name }}</div>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <div class="font-medium">€{{ number_format($invoice->total_amount, 2) }}</div>
                    <div class="text-xs text-zinc-500">Subtotal: €{{ number_format($invoice->subtotal, 2) }}</div>
                </flux:table.cell>
                <flux:table.cell>
                    @if($invoice->status === 'paid')
                        <flux:badge color="green">Pagada</flux:badge>
                    @else
                        <flux:badge color="yellow">Pendiente</flux:badge>
                    @endif
                    @if(!$invoice->include_in_taxes)
                        <div class="mt-1">
                            <flux:badge size="sm" color="amber">Excluida Impuestos</flux:badge>
                        </div>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        
                        <flux:menu>
                            <flux:menu.item icon="pencil-square" wire:click="edit('{{ $invoice->id }}')">Editar</flux:menu.item>
                            <flux:menu.item icon="arrow-down-tray" wire:click="downloadPdf('{{ $invoice->id }}')">Descargar PDF</flux:menu.item>
                            <flux:menu.item icon="envelope" wire:click="sendEmail('{{ $invoice->id }}')">Enviar por Email</flux:menu.item>
                            @if($invoice->status !== 'paid')
                                <flux:menu.item icon="check-circle" wire:click="markAsPaid('{{ $invoice->id }}')">Marcar Pagada</flux:menu.item>
                            @endif
                            <flux:menu.item icon="trash" wire:click="delete('{{ $invoice->id }}')" wire:confirm="¿Seguro que deseas eliminar esta factura?" class="text-red-500">Eliminar</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="6" class="py-10 text-center text-zinc-500">
                    No se han encontrado facturas.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $invoices->links() }}
    </div>

    <!-- Modal de Nueva Factura -->
    <flux:modal name="invoice-form" class="md:min-w-[520px]" x-on:close-modal-invoice-form.window="$el.close()">
        <form wire:submit="save">
            <flux:heading size="lg">{{ $editingInvoiceId ? 'Editar Factura' : 'Nueva Factura' }}</flux:heading>

            <div class="space-y-4 mt-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select wire:model.live="form.property_id" label="Inmueble *" required>
                        <option value="">Seleccione un inmueble</option>
                        @foreach($properties as $prop)
                            <option value="{{ $prop->id }}">
                                {{ $prop->name }}{{ !$prop->tenant ? ' (sin cliente)' : '' }}
                            </option>
                        @endforeach
                    </flux:select>

                    <flux:input type="date" wire:model="form.issue_date" label="Fecha factura *" required />
                </div>

                @if($this->selectedClient)
                    <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-sm">
                        <div>
                            <div class="text-xs text-zinc-500">Cliente asociado</div>
                            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->selectedClient->name }}</div>
                        </div>
                        <div class="text-right text-xs text-zinc-500">
                            NIF: {{ $this->selectedClient->document_number ?: 'Sin NIF' }}
                        </div>
                    </div>
                @elseif($form->property_id)
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                        Este inmueble no tiene cliente asociado. No se puede crear la factura.
                    </div>
                @endif

                <flux:input wire:model="form.description" label="Concepto *" required autocomplete="off" />

                <div class="flex flex-row items-end gap-3">
                    <div class="flex-1">
                        <flux:input type="number" step="0.01" wire:model.live="form.subtotal" label="Base Imp. (€)" required />
                    </div>
                    <div class="flex-1">
                        <flux:input type="number" step="0.01" wire:model.live="form.iva_percentage" label="IVA (%)" />
                    </div>
                    <div class="flex-1">
                        <flux:input type="number" step="0.01" wire:model.live="form.retention_percentage" label="IRPF (%)" />
                    </div>
                </div>

                <flux:switch wire:model.live="form.include_in_taxes" label="Incluir en Impuestos" />

                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 space-y-1.5 text-sm">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Base Imponible:</span>
                        <span>€{{ number_format((float) $form->subtotal, 2) }}</span>
                    </div>
                    @if((float) $form->iva_percentage > 0)
                        <div class="flex justify-between text-blue-600">
                            <span>IVA ({{ number_format((float) $form->iva_percentage, 2) }}%):</span>
                            <span>+€{{ number_format($form->ivaAmount(), 2) }}</span>
                        </div>
                    @endif
                    @if((float) $form->retention_percentage > 0)
                        <div class="flex justify-between text-red-500">
                            <span>IRPF ({{ number_format((float) $form->retention_percentage, 2) }}%):</span>
                            <span>-€{{ number_format($form->retentionAmount(), 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-base text-zinc-900 dark:text-zinc-100 pt-1.5 border-t border-zinc-200 dark:border-zinc-700">
                        <span>Total Factura:</span>
                        <span>€{{ number_format($form->totalAmount(), 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" :disabled="!$form->property_id || !$form->client_id">{{ $editingInvoiceId ? 'Guardar Cambios' : 'Guardar Factura' }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal de Previsualización de Email -->
    <flux:modal name="preview-email" class="md:w-[600px] space-y-6">
        <div>
            <flux:heading size="lg">Previsualizar Envío de Factura</flux:heading>
            <flux:subheading>Revisa los detalles del correo antes de enviarlo al cliente.</flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="emailTo" label="Destinatario" read-only />
            
            <flux:input wire:model="emailSubject" label="Asunto del Correo" />
            
            <flux:textarea wire:model="emailBody" label="Cuerpo del Mensaje" rows="5" />
            
            <div class="p-3 bg-zinc-50 rounded-lg border border-zinc-200 flex items-center gap-3">
                <flux:icon.document-text class="text-zinc-400" />
                <div class="flex-1">
                    <div class="text-sm font-medium text-zinc-900">Adjunto: {{ $attachmentName }}</div>
                    <div class="text-xs text-zinc-500">Documento PDF generado automáticamente</div>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancelar</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" wire:click="confirmSend" icon="paper-airplane">Enviar Factura ahora</flux:button>
        </div>
    </flux:modal>
</div>
