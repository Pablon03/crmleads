<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filtra automáticamente los registros por el usuario autenticado.
 * Aplica un global scope que añade WHERE user_id = auth()->id() a todas las queries.
 */
trait HasUserScope
{
    public static function bootHasUserScope(): void
    {
        static::addGlobalScope('belongsToUser', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where($builder->getModel()->getTable().'.user_id', auth()->id());
            }
        });

        // Asigna user_id automáticamente al crear un registro
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }
}
