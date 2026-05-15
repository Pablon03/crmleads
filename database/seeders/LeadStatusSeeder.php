<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Crea los 6 estados kanban por defecto para cada usuario existente.
 * También se puede llamar al crear un nuevo usuario.
 */
class LeadStatusSeeder extends Seeder
{
    public static array $defaults = [
        ['name' => 'Nuevo',              'color' => '#6b7280', 'position' => 1, 'is_default' => true],
        ['name' => 'Contactado',         'color' => '#3b82f6', 'position' => 2, 'is_default' => false],
        ['name' => 'Interesado',         'color' => '#eab308', 'position' => 3, 'is_default' => false],
        ['name' => 'Propuesta enviada',  'color' => '#f97316', 'position' => 4, 'is_default' => false],
        ['name' => 'Cerrado ganado',     'color' => '#22c55e', 'position' => 5, 'is_default' => false],
        ['name' => 'Cerrado perdido',    'color' => '#ef4444', 'position' => 6, 'is_default' => false],
    ];

    public function run(): void
    {
        User::all()->each(function (User $user) {
            self::createForUser($user->id);
        });
    }

    public static function createForUser(int $userId): void
    {
        // Solo crea si el usuario aún no tiene estados
        if (LeadStatus::withoutGlobalScopes()->where('user_id', $userId)->exists()) {
            return;
        }

        foreach (self::$defaults as $status) {
            LeadStatus::create(array_merge($status, ['user_id' => $userId]));
        }
    }
}
