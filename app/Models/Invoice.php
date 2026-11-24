<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'property_id',
        'issue_date',
        'subtotal',
        'iva_percentage',
        'iva_amount',
        'retention_percentage',
        'retention_amount',
        'total_amount',
        'status',
        'include_in_taxes',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'include_in_taxes' => 'boolean',
    ];

    public function scopeTaxable(Builder $query): Builder
    {
        return $query->where('include_in_taxes', true);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public static function generateNextNumber(?string $issueDate = null): string
    {
        $date = $issueDate ? Carbon::parse($issueDate) : Carbon::now();
        $year = $date->year;

        // Se basa en el mayor número ya usado con ese prefijo (no en el recuento),
        // así borrar o editar facturas nunca provoca números duplicados.
        $max = static::where('invoice_number', 'like', $year . '/%')
            ->pluck('invoice_number')
            ->map(fn (string $n) => (int) substr($n, strlen($year . '/')))
            ->max() ?? 0;

        return $year . '/' . ($max + 1);
    }
}
