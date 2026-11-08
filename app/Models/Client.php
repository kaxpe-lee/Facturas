<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'document_number',
        'billing_address',
    ];

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoicedProperties(): HasManyThrough
    {
        return $this->hasManyThrough(
            Property::class,
            Invoice::class,
            'client_id',
            'id',
            'id',
            'property_id'
        );
    }

    public function tenantedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'current_tenant_id');
    }
}
