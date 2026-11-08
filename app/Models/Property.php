<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'address',
        'monthly_rent',
        'default_iva_percentage',
        'default_retention_percentage',
        'is_active',
        'include_in_taxes',
        'current_tenant_id',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'default_iva_percentage' => 'decimal:2',
        'default_retention_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'include_in_taxes' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTaxable(Builder $query): Builder
    {
        return $query->where('include_in_taxes', true);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'current_tenant_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
