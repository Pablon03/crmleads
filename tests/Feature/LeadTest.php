<?php

use App\Models\Folder;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Database\Seeders\LeadStatusSeeder;

// ── Helpers ────────────────────────────────────────────────────────────────

function createUserWithStatuses(): User
{
    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);
    return $user;
}

function makeLead(User $user, array $attrs = []): Lead
{
    return Lead::create(array_merge([
        'user_id'       => $user->id,
        'business_name' => 'Negocio de prueba',
        'google_place_id' => 'place_' . uniqid(),
    ], $attrs));
}

// ── Test 1: Smoke — Creación de lead ───────────────────────────────────────

it('crea un lead correctamente', function () {
    $user = createUserWithStatuses();
    $this->actingAs($user);

    $folder = Folder::create(['user_id' => $user->id, 'name' => 'Carpeta test']);

    $lead = makeLead($user, [
        'folder_id'    => $folder->id,
        'business_name' => 'Peluquería Test',
        'phone'        => '+34 600 000 000',
        'category'     => 'Peluquería',
        'rating'       => 4.5,
    ]);

    expect($lead->id)->toBeInt()
        ->and($lead->business_name)->toBe('Peluquería Test')
        ->and($lead->user_id)->toBe($user->id)
        ->and($lead->folder_id)->toBe($folder->id)
        ->and((float) $lead->rating)->toBe(4.5);

    $this->assertDatabaseHas('leads', [
        'user_id'       => $user->id,
        'business_name' => 'Peluquería Test',
    ]);
});

// ── Test 2: Global Scope — usuario solo ve sus leads ──────────────────────

it('el global scope filtra leads por usuario', function () {
    $user1 = createUserWithStatuses();
    $user2 = createUserWithStatuses();

    makeLead($user1, ['business_name' => 'Lead de User1']);
    makeLead($user2, ['business_name' => 'Lead de User2']);

    $this->actingAs($user1);

    $leads = Lead::all();

    expect($leads)->toHaveCount(1)
        ->and($leads->first()->business_name)->toBe('Lead de User1');
});

// ── Test 3: Scope withFollowUpDue ─────────────────────────────────────────

it('scope withFollowUpDue devuelve solo los vencidos', function () {
    $user = createUserWithStatuses();
    $this->actingAs($user);

    // Vencido: hace 1 día
    makeLead($user, ['business_name' => 'Vencido',   'follow_up_at' => now()->subDay()]);
    // Futuro: en 2 días
    makeLead($user, ['business_name' => 'Futuro',    'follow_up_at' => now()->addDays(2)]);
    // Sin follow-up
    makeLead($user, ['business_name' => 'Sin fecha', 'follow_up_at' => null]);

    $due = Lead::withFollowUpDue()->get();

    expect($due)->toHaveCount(1)
        ->and($due->first()->business_name)->toBe('Vencido');
});
