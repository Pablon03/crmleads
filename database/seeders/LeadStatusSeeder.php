<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pipeline COMPARTIDO de equipo. Refleja el proceso real del negocio:
 * captación → demo → diagnóstico → propuesta → cliente activo → ciclo de vida (riesgo/baja).
 *
 * Al pasar a modelo de equipo, existe un único set de estados para todo el equipo,
 * no uno por usuario.
 */
class LeadStatusSeeder extends Seeder
{
    public static array $defaults = [
        ['name' => 'Nuevo',                 'color' => '#6b7280', 'position' => 1, 'is_default' => true],
        ['name' => 'Contactado',            'color' => '#3b82f6', 'position' => 2, 'is_default' => false],
        ['name' => 'Demo enviada',          'color' => '#06b6d4', 'position' => 3, 'is_default' => false],
        ['name' => 'Reunión / Diagnóstico', 'color' => '#eab308', 'position' => 4, 'is_default' => false],
        ['name' => 'Propuesta enviada',     'color' => '#f97316', 'position' => 5, 'is_default' => false],
        ['name' => 'Cliente activo',        'color' => '#22c55e', 'position' => 6, 'is_default' => false],
        ['name' => 'En riesgo',             'color' => '#f59e0b', 'position' => 7, 'is_default' => false],
        ['name' => 'Baja',                  'color' => '#ef4444', 'position' => 8, 'is_default' => false],
        ['name' => 'Cerrado perdido',       'color' => '#9ca3af', 'position' => 9, 'is_default' => false],
    ];

    public function run(): void
    {
        // whereRaw: Postgres no acepta comparar boolean con integer (is_admin = 1).
        $primaryId = User::withoutGlobalScopes()->whereRaw('is_admin = true')->min('id')
            ?? User::withoutGlobalScopes()->min('id');

        if ($primaryId) {
            self::ensureSharedDefaults($primaryId);
            self::syncMissing($primaryId);
        }
    }

    /**
     * Garantiza que exista el set compartido de estados (no-op si ya hay alguno).
     */
    public static function ensureSharedDefaults(int $userId): void
    {
        if (LeadStatus::withoutGlobalScopes()->exists()) {
            return;
        }

        self::createForUser($userId);
    }

    /**
     * Crea el set completo de estados para un usuario si aún no tiene ninguno.
     */
    public static function createForUser(int $userId): void
    {
        if (LeadStatus::withoutGlobalScopes()->where('user_id', $userId)->exists()) {
            return;
        }

        foreach (self::$defaults as $status) {
            LeadStatus::create(array_merge($status, ['user_id' => $userId]));
        }
    }

    /**
     * Añade al set compartido las etapas que falten (por nombre), sin duplicar.
     * Sirve para que instalaciones existentes reciban las nuevas etapas
     * (Demo, Diagnóstico, Cliente activo, En riesgo, Baja).
     */
    public static function syncMissing(int $userId): void
    {
        foreach (self::$defaults as $status) {
            $exists = LeadStatus::withoutGlobalScopes()
                ->where('name', $status['name'])
                ->exists();

            if (! $exists) {
                LeadStatus::create(array_merge($status, ['user_id' => $userId]));
            }
        }

        // Garantiza un único estado por defecto (el de menor posición).
        // DB::raw('false'/'true'): en Postgres el update masivo no pasa por el mutador
        // del modelo, así que hay que forzar el literal booleano.
        $default = LeadStatus::withoutGlobalScopes()->orderBy('position')->first();
        if ($default) {
            LeadStatus::withoutGlobalScopes()->where('id', '!=', $default->id)
                ->update(['is_default' => DB::raw('false')]);
            LeadStatus::withoutGlobalScopes()->where('id', $default->id)
                ->update(['is_default' => DB::raw('true')]);
        }
    }
}
