<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Component;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Configuración de Empresa')]
class Company extends Component
{
    public $company_name = '';
    public $company_nif = '';
    public $company_address = '';
    public $company_email = '';
    public $company_phone = '';

    public function mount()
    {
        $this->company_name = Setting::where('key', 'company_name')->value('value') ?? '';
        $this->company_nif = Setting::where('key', 'company_nif')->value('value') ?? '';
        $this->company_address = Setting::where('key', 'company_address')->value('value') ?? '';
        $this->company_email = Setting::where('key', 'company_email')->value('value') ?? '';
        $this->company_phone = Setting::where('key', 'company_phone')->value('value') ?? '';
    }

    public function save()
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_nif' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
        ]);

        Setting::updateOrCreate(['key' => 'company_name'], ['value' => $this->company_name]);
        Setting::updateOrCreate(['key' => 'company_nif'], ['value' => $this->company_nif]);
        Setting::updateOrCreate(['key' => 'company_address'], ['value' => $this->company_address]);
        Setting::updateOrCreate(['key' => 'company_email'], ['value' => $this->company_email]);
        Setting::updateOrCreate(['key' => 'company_phone'], ['value' => $this->company_phone]);

        \Flux::toast('Datos de emisor actualizados correctamente.');
    }

    public function render()
    {
        return view('livewire.settings.company');
    }
}
