<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Registro de Inmuebles</flux:heading>
        
        <flux:modal.trigger name="property-form">
            <flux:button variant="primary" wire:click="create" icon="plus">Nuevo Inmueble</flux:button>
        </flux:modal.trigger>
    </div>
    
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, dirección o inquilino..." icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Inquilino Actual</flux:table.column>
            <flux:table.column>Renta Base</flux:table.column>
            <flux:table.column>Impuestos</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Documentos</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($properties as $property)
            <flux:table.row wire:key="property-{{ $property->id }}">
                <flux:table.cell>
                    <div class="font-medium">{{ $property->name }}</div>
                    @if($property->address)
                        <div class="text-xs text-zinc-500 truncate max-w-xs">{{ $property->address }}</div>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @if($property->tenant)
                        <div class="flex items-center gap-2">
                            <flux:icon.user class="size-4 text-zinc-400" />
                            <span>{{ $property->tenant->name }}</span>
                        </div>
                    @else
                        <span class="text-zinc-500">Sin inquilino</span>
                    @endif
                </flux:table.cell>
                <flux:table.cell>€{{ number_format($property->monthly_rent, 2) }}</flux:table.cell>
                <flux:table.cell>
                    <div class="text-sm">IVA: {{ number_format($property->default_iva_percentage, 2) }}%</div>
                    <div class="text-sm text-zinc-500">IRPF: {{ number_format($property->default_retention_percentage, 2) }}%</div>
                    @if(!$property->include_in_taxes)
                        <flux:badge size="sm" color="amber" class="mt-1">Excluido Impuestos</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @if($property->is_active)
                        <flux:badge color="green">Activo</flux:badge>
                    @else
                        <flux:badge color="zinc">Inactivo</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell class="text-zinc-500">
                    {{ $property->invoices_count }}
                </flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        
                        <flux:menu>
                            <flux:modal.trigger name="property-form">
                                <flux:menu.item icon="pencil" wire:click="edit('{{ $property->id }}')">Editar</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.item icon="trash" wire:click="delete('{{ $property->id }}')" wire:confirm="¿Seguro que deseas eliminar este inmueble?" class="text-red-500">Eliminar</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="7" class="py-10 text-center text-zinc-500">
                    No se han encontrado inmuebles.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $properties->links() }}
    </div>

    <flux:modal name="property-form" class="md:min-w-[500px]" x-on:close-modal-property-form.window="$el.close()">
        <form wire:submit="save">
            <flux:heading size="lg">{{ $editing ? 'Editar Inmueble' : 'Nuevo Inmueble' }}</flux:heading>
            
            <div class="space-y-4 mt-6">
                <flux:input wire:model="form.name" label="Nombre corto" placeholder="Ej. Piso Centro 3A" required autocomplete="off"/>
                <flux:textarea wire:model="form.description" label="Descripción Comercial (Factura)" rows="2" placeholder="Ej. Local Comercial 200m2 en Planta Baja (Opcional, sobrescribe el nombre en la factura)" />
                <flux:textarea wire:model="form.address" label="Dirección Completa" rows="2" />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.monthly_rent" label="Renta Base (€)" type="number" step="0.01" required />
                    <flux:select wire:model="form.current_tenant_id" label="Inquilino Actual">
                        <option value="">Seleccione inquilino (opcional)</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.default_iva_percentage" label="IVA (%) por defecto" type="number" step="0.01" />
                    <flux:input wire:model="form.default_retention_percentage" label="IRPF (%) por defecto" type="number" step="0.01" />
                </div>
                
                <flux:switch wire:model="form.is_active" label="Inmueble Activo" />
                <flux:switch wire:model="form.include_in_taxes" label="Incluir en Impuestos" />
            </div>

            <div class="flex justify-end gap-2 mt-8">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Guardar</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
