<div>
    <div class="mb-6">
        <flux:heading size="xl">Informe de Impuestos</flux:heading>
        <flux:subheading>Resumen trimestral de IVA y retenciones para modelos oficiales.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <flux:card>
            <flux:heading size="lg" class="mb-4">Filtros</flux:heading>
            <div class="space-y-4">
                <flux:select wire:model.live="year" label="Año Fiscal">
                    @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </flux:select>
                
                <flux:select wire:model.live="quarter" label="Trimestre">
                    <option value="1">1T (Ene - Mar)</option>
                    <option value="2">2T (Abr - Jun)</option>
                    <option value="3">3T (Jul - Sep)</option>
                    <option value="4">4T (Oct - Dic)</option>
                </flux:select>
            </div>
        </flux:card>

        <flux:card class="md:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <flux:heading size="lg">Resumen</flux:heading>
                    <flux:badge color="zinc">{{ $this->reportData['count'] }} facturas computadas</flux:badge>
                </div>
                <flux:button variant="primary" size="sm" icon="arrow-down-tray" wire:click="exportPdf" wire:loading.attr="disabled">Exportar PDF</flux:button>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                    <div class="text-sm font-medium text-zinc-500 mb-1">Base Imponible</div>
                    <div class="text-2xl font-semibold text-zinc-900">€{{ number_format($this->reportData['base'], 2) }}</div>
                </div>
                
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                    <div class="text-sm font-medium text-zinc-500 mb-1">IVA Repercutido</div>
                    <div class="text-2xl font-semibold text-blue-600">€{{ number_format($this->reportData['iva'], 2) }}</div>
                </div>
                
                <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-100">
                    <div class="text-sm font-medium text-zinc-500 mb-1">Retenciones (IRPF)</div>
                    <div class="text-2xl font-semibold text-red-600">-€{{ number_format($this->reportData['retention'], 2) }}</div>
                </div>

                <div class="p-4 rounded-xl bg-zinc-900 border border-zinc-800 text-white">
                    <div class="text-sm font-medium text-zinc-400 mb-1">Total (+ Imp.)</div>
                    <div class="text-2xl font-semibold">€{{ number_format($this->reportData['total'], 2) }}</div>
                </div>
            </div>
            
            <div class="mt-8 text-sm text-zinc-500">
                <p><strong>Nota:</strong> Este informe es puramente informativo para facilitar la cumplimentación de los modelos tributarios (ej: Modelo 303 y 130). Consulta siempre con tu asesor fiscal para corroborar los datos.</p>
            </div>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading size="lg" class="mb-4">Facturas Registradas ({{ $quarter }}T {{ $year }})</flux:heading>
        
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Número</flux:table.column>
                <flux:table.column>Fecha Emisión</flux:table.column>
                <flux:table.column>Cliente</flux:table.column>
                <flux:table.column>Base Imp.</flux:table.column>
                <flux:table.column>IVA</flux:table.column>
                <flux:table.column>IRPF</flux:table.column>
                <flux:table.column>Total</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->reportData['invoices'] as $invoice)
                <flux:table.row>
                    <flux:table.cell class="font-medium whitespace-nowrap">{{ $invoice->invoice_number }}</flux:table.cell>
                    <flux:table.cell>{{ $invoice->issue_date->format('d/m/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="font-medium">{{ $invoice->client->name }}</div>
                        <div class="text-xs text-zinc-500">{{ $invoice->client->document_number }}</div>
                    </flux:table.cell>
                    <flux:table.cell>€{{ number_format($invoice->subtotal, 2) }}</flux:table.cell>
                    <flux:table.cell class="text-blue-600">€{{ number_format($invoice->iva_amount, 2) }}</flux:table.cell>
                    <flux:table.cell class="text-red-500">-€{{ number_format($invoice->retention_amount, 2) }}</flux:table.cell>
                    <flux:table.cell class="font-semibold">€{{ number_format($invoice->total_amount, 2) }}</flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="py-10 text-center text-zinc-500">
                        No hay facturas registradas en este periodo.
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
