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
        'sold_at',
        'notes',
    ];

    protected $casts = [
        'sold_price' => 'decimal:2',
        'sold_at'    => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
