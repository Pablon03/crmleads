<?php

use App\Models\User;
use Database\Seeders\LeadStatusSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Aplica a instalaciones existentes las nuevas etapas del pipeline y los 3 planes.
 * Idempotente: solo añade lo que falte.
 */
return new class extends Migration
{
    public function up(): void
    {
        $primaryId = User::withoutGlobalScopes()->where('is_admin', true)->min('id')
            ?? User::withoutGlobalScopes()->min('id');

        if (! $primaryId) {
            return; // instalación vacía: el DatabaseSeeder se encargará al arrancar
        }

        LeadStatusSeeder::ensureSharedDefaults($primaryId);
        LeadStatusSeeder::syncMissing($primaryId);
        ServiceSeeder::ensurePlans($primaryId);
    }

    public function down(): void
    {
        // No se revierten datos sembrados.
    }
};
