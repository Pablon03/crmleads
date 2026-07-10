<?php

namespace App\Models\Concerns;

/**
 * Modelo de EQUIPO compartido.
 *
 * Todo el equipo (Pablo, Tomás, Ornella) trabaja sobre un único pipeline: todos ven
 * los mismos leads, estados, servicios, carpetas y transacciones. Por eso ya NO se
 * aplica un filtro global por usuario.
 *
 * Se conserva `user_id` como "creador" del registro (útil para auditoría) y se asigna
 * automáticamente al crear. La responsabilidad operativa de un lead se gestiona con el
 * campo `assigned_to` (ver Lead), no con la propiedad del registro.
 *
 * Nota: se mantiene el nombre del trait; `withoutGlobalScopes()` sigue siendo válido en
 * el resto del código aunque ahora no haya scope que quitar.
 */
trait HasUserScope
{
    public static function bootHasUserScope(): void
    {
        // Asigna user_id (creador) automáticamente al crear un registro.
        static::creating(function ($model) {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = auth()->id();
            }
        });
    }
}
