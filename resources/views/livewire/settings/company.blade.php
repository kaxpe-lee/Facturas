<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1">Datos del Emisor Fiscal</flux:heading>
        <flux:subheading size="lg" class="mb-6">Establece tu nombre o el de tu empresa para que aparezca por defecto en la cabecera de todas tus facturas en PDF.</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6 mt-6">
        <flux:input wire:model="company_name" label="Nombre de Empresa / Autónomo" placeholder="Ej. Juan Pérez" required />
        
        <flux:input wire:model="company_nif" label="NIF / DNI" placeholder="B12345678" />

        <flux:textarea wire:model="company_address" label="Dirección Fiscal Completa" placeholder="C/ Principal 123, Madrid, 28000" rows="3" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model="company_email" label="Email de Contacto" type="email" placeholder="correo@ejemplo.com" />
            
            <flux:input wire:model="company_phone" label="Teléfono de Contacto" placeholder="+34 600..." />
        </div>

        <div class="flex items-center gap-4 mt-8">
            <flux:button type="submit" variant="primary">Guardar Cambios</flux:button>
            <div wire:loading wire:target="save" class="text-sm text-zinc-500">Guardando...</div>
        </div>
    </form>
</div>
