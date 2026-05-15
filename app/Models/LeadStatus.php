<?php

namespace App\Models;

use App\Models\Concerns\HasUserScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LeadStatus extends Model
{
    use HasUserScope;

    protected $fillable = [
        'user_id',
        'name',
        'color',
        'position',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'position'   => 'integer',
    ];

    protected function isDefault(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => match(true) {
                is_bool($value) => $value,
                $value === 't'  => true,
                $value === 'f'  => false,
                default         => (bool) $value,
            },
            set: fn ($value) => ['is_default' => DB::raw($value ? 'true' : 'false')],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status_id');
    }
}
