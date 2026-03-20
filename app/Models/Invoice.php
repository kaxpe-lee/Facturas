<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'property_id',
        'issue_date',
        'due_date',
        'subtotal',
        'iva_percentage',
        'iva_amount',
        'retention_percentage',
        'retention_amount',
        'total_amount',
        'status', // 'pending', 'paid'
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function property() {
        return $this->belongsTo(Property::class);
    }

    public function items() {
        return $this->hasMany(InvoiceItem::class);
    }
}
