<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Clientes e Inquilinos</flux:heading>
        
        <flux:modal.trigger name="client-form">
            <flux:button variant="primary" wire:click="create" icon="plus">Nuevo Cliente</flux:button>
        </flux:modal.trigger>
    </div>
    
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o NIF..." icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>NIF/DNI</flux:table.column>
            <flux:table.column>Contacto</flux:table.column>
            <flux:table.column>Tipo</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($clients as $client)  
            <flux:table.row>
                <flux:table.cell class="font-medium">{{ $client->name }}</flux:table.cell>
                <flux:table.cell class="text-zinc-500">{{ $client->document_number ?: '-' }}</flux:table.cell>
                <flux:table.cell>
                    <div class="text-sm">{{ $client->email ?: '-' }}</div>
                    <div class="text-sm text-zinc-500">{{ $client->phone ?: '-' }}</div>
                </flux:table.cell>
                <flux:table.cell>
                    @if($client->type === 'tenant')
                        <flux:badge color="blue">Inquilino</flux:badge>
                    @else
                        <flux:badge color="zinc">Puntual</flux:badge>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        
                        <flux:menu>
                            <flux:modal.trigger name="client-form">
                                <flux:menu.item icon="pencil" wire:click="edit('{{ $client->id }}')">Editar</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.item icon="trash" wire:click="delete('{{ $client->id }}')" wire:confirm="¿Seguro que deseas eliminar este cliente?" class="text-red-500">Eliminar</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
            @empty
            <flux:table.row>
                <flux:table.cell colspan="5" class="py-10 text-center text-zinc-500">
                    No se han encontrado clientes con esos criterios.
                </flux:table.cell>
            </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    <!-- El x-on:close-modal-client-form.window="$el.close()" permite cerrar el modal por evento -->
    <flux:modal name="client-form" class="md:min-w-[500px]" x-on:close-modal-client-form.window="$el.close()">
        <form wire:submit="save">
            <flux:heading size="lg">{{ $editing ? 'Editar Cliente' : 'Nuevo Cliente' }}</flux:heading>
            
            <div class="space-y-4 mt-6">
                <flux:input wire:model="form.name" label="Nombre completo" placeholder="Ej. Juan Pérez" required autocomplete="off"/>
                <flux:input wire:model="form.document_number" label="NIF / DNI" placeholder="12345678X" autocomplete="off" />
                
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="form.email" label="Email" type="email" placeholder="correo@ejemplo.com" autocomplete="off" />
                    <flux:input wire:model="form.phone" label="Teléfono" placeholder="+34 600..." autocomplete="off" />
                </div>
                
                <flux:textarea wire:model="form.billing_address" label="Dirección de Facturación" />
                
                <flux:select wire:model="form.type" label="Tipo de Cliente" required>
                    <option value="tenant">Inquilino (Recurrente)</option>
                    <option value="occasional">Cliente Puntual</option>
                </flux:select>
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
