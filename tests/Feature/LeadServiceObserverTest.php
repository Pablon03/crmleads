<?php

use App\Models\Lead;
use App\Models\LeadService;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\LeadStatusSeeder;

// ── Test 5: Observer — transición de status y auto-creación de Transaction ─

it('crea una transacción de income al marcar un servicio como sold', function () {
    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);

    $lead = Lead::create([
        'user_id'       => $user->id,
        'business_name' => 'Cliente Test',
        'google_place_id' => 'obs_test_001',
    ]);

    $service = Service::create([
        'user_id'    => $user->id,
        'name'       => 'Diseño web',
        'base_price' => 500.00,
        'is_active'  => true,
    ]);

    // Crear pivot en estado 'interested'
    $leadService = LeadService::create([
        'lead_id'    => $lead->id,
        'service_id' => $service->id,
        'status'     => 'interested',
    ]);

    expect(Transaction::withoutGlobalScopes()->where('lead_service_id', $leadService->id)->count())->toBe(0);

    // Cambiar a 'sold' — el Observer debe crear la Transaction
    $leadService->update([
        'status'     => 'sold',
        'sold_price' => 450.00,
        'sold_at'    => now(),
    ]);

    $transaction = Transaction::withoutGlobalScopes()
        ->where('lead_service_id', $leadService->id)
        ->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe('income')
        ->and((float) $transaction->amount)->toBe(450.0)
        ->and($transaction->user_id)->toBe($user->id);
});

it('elimina la transacción si el servicio deja de estar sold', function () {
    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Cliente Revertido',
        'google_place_id' => 'obs_test_002',
    ]);

    $service = Service::create([
        'user_id'    => $user->id,
        'name'       => 'SEO',
        'base_price' => 200.00,
        'is_active'  => true,
    ]);

    $leadService = LeadService::create([
        'lead_id'    => $lead->id,
        'service_id' => $service->id,
        'status'     => 'sold',
        'sold_price' => 200.00,
        'sold_at'    => now(),
    ]);

    // Debe existir la transacción automática (creada en 'created')
    expect(Transaction::withoutGlobalScopes()->where('lead_service_id', $leadService->id)->count())->toBe(1);

    // Revertir a rejected
    $leadService->update(['status' => 'rejected']);

    // La transacción debe haber sido eliminada
    expect(Transaction::withoutGlobalScopes()->where('lead_service_id', $leadService->id)->count())->toBe(0);
});

it('usa el precio base del servicio si no hay sold_price al crear transaction', function () {
    $user = User::factory()->create();
    LeadStatusSeeder::createForUser($user->id);

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Cliente Precio Base',
        'google_place_id' => 'obs_test_003',
    ]);

    $service = Service::create([
        'user_id'    => $user->id,
        'name'       => 'Redes Sociales',
        'base_price' => 350.00,
        'is_active'  => true,
    ]);

    $leadService = LeadService::create([
        'lead_id'    => $lead->id,
        'service_id' => $service->id,
        'status'     => 'interested',
    ]);

    // Sin sold_price — debe usar base_price del servicio
    $leadService->update(['status' => 'sold']);

    $transaction = Transaction::withoutGlobalScopes()
        ->where('lead_service_id', $leadService->id)
        ->first();

    expect((float) $transaction->amount)->toBe(350.0);
});
