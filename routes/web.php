<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('/clients', \App\Livewire\Clients\Index::class)->name('clients');
    Route::get('/properties', \App\Livewire\Properties\Index::class)->name('properties');
    Route::get('/invoices', \App\Livewire\Invoices\Index::class)->name('invoices');
    Route::get('/invoices/create', \App\Livewire\Invoices\Create::class)->name('invoices.create');
    Route::get('/taxes', \App\Livewire\Reports\Taxes::class)->name('taxes');
});

require __DIR__.'/settings.php';
