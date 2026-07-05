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

        // Cada usuario nuevo arranca con su propio pipeline (Nuevo, Contactado, etc.)
        LeadStatusSeeder::createForUser($user->id);
    }
}
