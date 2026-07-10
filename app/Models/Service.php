<?php

namespace App\Models;

use App\Models\Concerns\HasUserScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use HasUserScope;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'base_price',   // cuota mensual para servicios recurrentes
        'is_recurring',
        'setup_fee',
        'payment_link',
        'is_active',
    ];

    protected $casts = [
        'base_price'   => 'decimal:2',
        'setup_fee'    => 'decimal:2',
        'is_recurring' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->whereRaw('is_active = true');
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match(true) {
                is_bool($value) => $value,
                $value === 't'  => true,
                $value === 'f'  => false,
                default         => (bool) $value,
            },
            set: fn ($value) => ['is_active' => DB::raw($value ? 'true' : 'false')],
        );
    }

    // Mismo patrón que is_active para compatibilidad de booleanos en Postgres.
    protected function isRecurring(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match(true) {
                is_bool($value) => $value,
                $value === 't'  => true,
                $value === 'f'  => false,
                default         => (bool) $value,
            },
            set: fn ($value) => ['is_recurring' => DB::raw($value ? 'true' : 'false')],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class)
            ->using(LeadService::class)
            ->withPivot(['status', 'sold_price', 'sold_at', 'notes'])
            ->withTimestamps();
    }

    public function leadServices(): HasMany
    {
        return $this->hasMany(LeadService::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
