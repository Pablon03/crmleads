<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Database\Seeders\LeadStatusSeeder;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;

        // Pipeline COMPARTIDO de equipo: no se crea un pipeline por usuario.
        // Solo se garantiza que el set compartido de estados exista (no-op si ya está).
        LeadStatusSeeder::ensureSharedDefaults($user->id);
    }
}
