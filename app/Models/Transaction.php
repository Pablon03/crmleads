<?php

namespace App\Models;

use App\Models\Concerns\HasUserScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUserScope;

    protected $fillable = [
        'user_id',
        'lead_id',
        'service_id',
        'lead_service_id',
        'type',
        'amount',
        'description',
        'transacted_at',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'transacted_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function leadService(): BelongsTo
    {
        return $this->belongsTo(LeadService::class);
    }
}
