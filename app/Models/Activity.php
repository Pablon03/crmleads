<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'lead_id',
        'user_id',
        'type',
        'content',
        'occurred_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Activity $activity) {
            if (empty($activity->user_id)) {
                $activity->user_id = auth()->id();
            }
        });
    }

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
