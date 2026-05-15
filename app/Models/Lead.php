<?php

namespace App\Models;

use App\Models\Concerns\HasUserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasUserScope;

    protected $fillable = [
        'user_id',
        'folder_id',
        'status_id',
        'business_name',
        'address',
        'phone',
        'email',
        'website',
        'category',
        'rating',
        'reviews_count',
        'google_place_id',
        'latitude',
        'longitude',
        'opening_hours',
        'images',
        'raw_data',
        'follow_up_at',
        'notes',
        'kanban_order',
        'has_whatsapp',
    ];

    protected $casts = [
        'rating'        => 'decimal:1',
        'opening_hours' => 'array',
        'images'        => 'array',
        'raw_data'      => 'array',
        'follow_up_at'  => 'datetime',
        'has_whatsapp'  => 'boolean',
    ];

    protected function hasWhatsapp(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match(true) {
                is_null($value) => null,
                is_bool($value) => $value,
                $value === 't'  => true,
                $value === 'f'  => false,
                default         => (bool) $value,
            },
            set: fn ($value) => ['has_whatsapp' => is_null($value) ? null : DB::raw($value ? 'true' : 'false')],
        );
    }

    // Accessor: dirección completa formateada
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', array_filter([
                $this->address,
            ]))
        );
    }

    // Scope: leads con follow-up pendiente (fecha <= ahora y no nula)
    public function scopeWithFollowUpDue(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<=', now());
    }

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->using(LeadService::class)
            ->withPivot(['id', 'status', 'sold_price', 'sold_at', 'notes'])
            ->withTimestamps();
    }

    public function leadServices(): HasMany
    {
        return $this->hasMany(LeadService::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderByDesc('occurred_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
