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
        'assigned_to',
        'folder_id',
        'status_id',
        'priority_score',
        'source',
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
        'rating'         => 'decimal:1',
        'priority_score' => 'integer',
        'opening_hours'  => 'array',
        'images'         => 'array',
        'raw_data'       => 'array',
        'follow_up_at'   => 'datetime',
        'has_whatsapp'   => 'boolean',
    ];

    protected static function booted(): void
    {
        // Recalcula la prioridad del lead en cada guardado (importación y edición manual).
        static::saving(function (Lead $lead) {
            $lead->priority_score = self::computePriorityScore($lead);
        });
    }

    /**
     * Puntuación 0-100 para priorizar el trabajo comercial.
     * Cuanto peor está la presencia online del negocio, mejor prospecto es.
     */
    public static function computePriorityScore(Lead $lead): int
    {
        $score = 0;

        // Sin web = máxima oportunidad.
        if (blank($lead->website)) {
            $score += 40;
        }

        // Pocas reseñas = ficha descuidada.
        $reviews = $lead->reviews_count;
        if (is_null($reviews) || $reviews < 10) {
            $score += 25;
        } elseif ($reviews < 30) {
            $score += 12;
        }

        // Negocio con reputación decente = merece la pena.
        $rating = (float) $lead->rating;
        if ($rating >= 4) {
            $score += 15;
        } elseif ($rating >= 3) {
            $score += 8;
        }

        // Contactabilidad: móvil (WhatsApp) suma más que fijo.
        $digits = preg_replace('/[^0-9]/', '', (string) $lead->phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '34')) {
            $digits = substr($digits, 2);
        }
        $first = $digits[0] ?? '';
        if (in_array($first, ['6', '7'], true)) {
            $score += 20;
        } elseif (in_array($first, ['8', '9'], true)) {
            $score += 10;
        }

        return min(100, $score);
    }

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

    // Responsable operativo del lead (Pablo / Tomás / Ornella).
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
