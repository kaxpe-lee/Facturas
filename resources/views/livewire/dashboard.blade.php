<div class="space-y-6">
    <div>
        <flux:heading size="xl">Panel de control</flux:heading>
        <flux:subheading>Resumen general de tu actividad.</flux:subheading>
    </div>

    {{-- Resúmenes --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <a href="{{ route('clients') }}" wire:navigate class="block">
            <flux:card class="h-full transition hover:border-zinc-400 dark:hover:border-zinc-500">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Clientes</flux:heading>
                    <flux:icon.users class="size-5 text-zinc-400" />
                </div>
                <div class="mt-3 text-4xl font-semibold">{{ $this->clientStats['total'] }}</div>
                <div class="mt-1 text-sm text-zinc-500">{{ $this->clientStats['with_property'] }} con inmueble asignado</div>
            </flux:card>
        </a>

        <a href="{{ route('properties') }}" wire:navigate class="block">
            <flux:card class="h-full transition hover:border-zinc-400 dark:hover:border-zinc-500">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Propiedades</flux:heading>
                    <flux:icon.building-office-2 class="size-5 text-zinc-400" />
                </div>
                <div class="mt-3 text-4xl font-semibold">{{ $this->propertyStats['total'] }}</div>
                <div class="mt-1 text-sm text-zinc-500">
                    {{ $this->propertyStats['active'] }} activas · €{{ number_format($this->propertyStats['monthly_rent'], 2) }}/mes
                </div>
            </flux:card>
        </a>

        <a href="{{ route('invoices') }}" wire:navigate class="block">
            <flux:card class="h-full transition hover:border-zinc-400 dark:hover:border-zinc-500">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Facturas</flux:heading>
                    <flux:icon.document-text class="size-5 text-zinc-400" />
                </div>
                <div class="mt-3 text-4xl font-semibold">{{ $this->invoiceStats['total'] }}</div>
                <div class="mt-1 text-sm text-zinc-500">
                    {{ $this->invoiceStats['pending'] }} pendientes · €{{ number_format($this->invoiceStats['pending_amount'], 2) }}
                </div>
            </flux:card>
        </a>
    </div>

    {{-- Resúmenes fiscales --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @foreach ([
            ['title' => 'Resumen fiscal del trimestre', 'data' => $this->quarter],
            ['title' => 'Resumen anual', 'data' => $this->year],
        ] as $block)
            @php($d = $block['data'])
            <flux:card>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <flux:heading size="lg">{{ $block['title'] }}</flux:heading>
                        <flux:subheading>{{ $d['label'] }} ({{ $d['range'] }})</flux:subheading>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:badge color="zinc">{{ $d['count'] }} facturas</flux:badge>
                        @if ($d['excluded'] > 0)
                            <flux:badge color="amber">{{ $d['excluded'] }} excluidas</flux:badge>
                        @endif
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-1 text-sm font-medium text-zinc-500">Base Imponible</div>
                        <div class="text-xl font-semibold">€{{ number_format($d['base'], 2) }}</div>
                    </div>
                    <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-1 text-sm font-medium text-zinc-500">IVA Repercutido</div>
                        <div class="text-xl font-semibold text-blue-600 dark:text-blue-400">€{{ number_format($d['iva'], 2) }}</div>
                    </div>
                    <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="mb-1 text-sm font-medium text-zinc-500">Retenciones (IRPF)</div>
                        <div class="text-xl font-semibold text-red-600 dark:text-red-400">-€{{ number_format($d['retention'], 2) }}</div>
                    </div>
                    <div class="rounded-xl border border-zinc-900 bg-zinc-900 p-4 text-white dark:border-zinc-600 dark:bg-zinc-950">
                        <div class="mb-1 text-sm font-medium text-zinc-400">Total (+ Imp.)</div>
                        <div class="text-xl font-semibold">€{{ number_format($d['total'], 2) }}</div>
                    </div>
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
