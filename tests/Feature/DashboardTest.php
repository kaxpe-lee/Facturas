<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows quarter and year fiscal summaries', function () {
    $this->actingAs(User::factory()->create());

    $client = \App\Models\Client::create(['name' => 'Inquilino']);
    $property = \App\Models\Property::create([
        'name' => 'Piso', 'address' => 'Calle 1', 'monthly_rent' => 500,
        'default_iva_percentage' => 21, 'default_retention_percentage' => 19,
        'is_active' => true, 'current_tenant_id' => $client->id,
    ]);

    $make = fn (string $number, string $date, float $sub, bool $taxes = true) => \App\Models\Invoice::create([
        'invoice_number' => $number, 'client_id' => $client->id, 'property_id' => $property->id,
        'issue_date' => $date, 'subtotal' => $sub, 'iva_percentage' => 0, 'iva_amount' => 0,
        'retention_percentage' => 0, 'retention_amount' => 0, 'total_amount' => $sub,
        'status' => 'pending', 'include_in_taxes' => $taxes,
    ]);

    $this->travelTo(\Carbon\Carbon::create(2026, 11, 24));
    $make('2026/1', '2026-11-01', 1000);   // trimestre y año
    $make('2026/2', '2026-02-01', 300);    // solo año
    $make('2026/3', '2026-11-05', 50, false); // excluida
    $make('2025/1', '2025-11-01', 999);    // otro año

    \Livewire\Livewire::test(\App\Livewire\Dashboard::class)
        ->assertSet('clientStats', ['total' => 1, 'with_property' => 1])
        ->assertSee('4T 2026')
        ->assertSee('€1,000.00')
        ->assertSee('€1,300.00');
});
