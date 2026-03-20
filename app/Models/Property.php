<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'address',
        'monthly_rent',
        'default_iva_percentage',
        'default_retention_percentage',
        'is_active',
        'current_tenant_id',
    ];

    public function tenant() {
        return $this->belongsTo(Client::class, 'current_tenant_id');
    }
}
