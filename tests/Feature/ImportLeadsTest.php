<?php

use App\Jobs\ImportLeadsFromGoogleMapsJob;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Database\Seeders\LeadStatusSeeder;
use Illuminate\Support\Facades\Notification;

// ── Test 4: Importación con DummyDriver ────────────────────────────────────

it('importa leads con el DummyDriver y evita duplicados en upsert', function () {
    // Evitar que se intente escribir en la tabla notifications (no existe en SQLite)
    Notification::fake();

    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);

    // Primera importación — debe crear 5 leads (DummyDriver devuelve 5 fijos)
    ImportLeadsFromGoogleMapsJob::dispatchSync(
        userId:   $user->id,
        folderId: null,
        query:    'peluquerías',
        location: 'Madrid',
        radius:   5000,
        limit:    20,
    );

    $firstCount = Lead::withoutGlobalScopes()->where('user_id', $user->id)->count();
    expect($firstCount)->toBe(5);

    // Segunda importación idéntica — upsert no crea duplicados
    ImportLeadsFromGoogleMapsJob::dispatchSync(
        userId:   $user->id,
        folderId: null,
        query:    'peluquerías',
        location: 'Madrid',
        radius:   5000,
        limit:    20,
    );

    $secondCount = Lead::withoutGlobalScopes()->where('user_id', $user->id)->count();
    expect($secondCount)->toBe(5); // sigue siendo 5, no 10
});

it('el job asigna el estado por defecto al importar', function () {
    Notification::fake();

    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);

    $defaultStatus = LeadStatus::withoutGlobalScopes()
        ->where('user_id', $user->id)
        ->where('is_default', true)
        ->first();

    ImportLeadsFromGoogleMapsJob::dispatchSync(
        userId:   $user->id,
        folderId: null,
        query:    'restaurantes',
        location: 'Madrid',
        radius:   5000,
        limit:    5,
    );

    $leads = Lead::withoutGlobalScopes()->where('user_id', $user->id)->get();

    expect($leads->every(fn ($lead) => $lead->status_id === $defaultStatus->id))->toBeTrue();
});
