<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Registro de Facturas</flux:heading>
        
        <flux:button variant="primary" href="{{ route('invoices.create') }}" icon="plus">Nueva Factura</flux:button>
    </div>
    
    <div class="mb-4">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por número o cliente..." icon="magnifying-glass" class="max-w-sm" />
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Número</flux:table.column>
            <flux:table.column>Fecha Emisión</flux:table.column>
            <flux:table.column>Cliente / Inmueble</flux:table.column>
            <flux:table.column>Totales</flux:table.column>
            <flux:table.column>Estado</flux:table.column>
            <flux:table.column>Acciones</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($invoices as $invoice)
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
                </flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                        
                        <flux:menu>
                            <flux:menu.item icon="arrow-down-tray" wire:click="downloadPdf('{{ $invoice->id }}')">Descargar PDF</flux:menu.item>
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
</div>
