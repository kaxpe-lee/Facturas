<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
    <flux:toast position="top right" />
</x-layouts::app.sidebar>
