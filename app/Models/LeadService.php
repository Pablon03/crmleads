<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot enriquecido entre Lead y Service.
 * Extiende Pivot (no Model) para que BelongsToMany lo use correctamente con using().
 */
class LeadService extends Pivot
{
    public $incrementing = true; // necesario para que el id autoincremental funcione en pivots

    protected $table = 'lead_service';

    protected $fillable = [
        'lead_id',
        'service_id',
        'status',
        'sold_price',
        'monthly_price',
        'billing_day',
        'sold_at',
        'started_at',
        'canceled_at',
        'notes',
    ];

    protected $casts = [
        'sold_price'    => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'billing_day'   => 'integer',
        'sold_at'       => 'datetime',
        'started_at'    => 'date',
        'canceled_at'   => 'date',
    ];

    /** Estados que cuentan como suscripción activa (facturable en el MRR). */
    public static array $activeStatuses = ['sold'];

    /**
     * Valor mensual real de esta suscripción:
     * cuota pactada > precio puntual > cuota base del servicio.
     */
    public function monthlyValue(): float
    {
        return (float) ($this->monthly_price
            ?? $this->sold_price
            ?? $this->service?->base_price
            ?? 0);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', self::$activeStatuses);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
