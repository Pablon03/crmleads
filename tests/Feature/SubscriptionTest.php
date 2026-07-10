<?php

use App\Models\Lead;
use App\Models\LeadService;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;

// ── Score de prioridad ─────────────────────────────────────────────────────

it('calcula la máxima prioridad para el peor prospecto (sin web, sin reseñas, móvil)', function () {
    $user = User::factory()->create();

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Peluquería sin web',
        'google_place_id' => 'prio_001',
        'website'         => null,
        'reviews_count'   => 0,
        'rating'          => 4.5,
        'phone'           => '600123123', // móvil español
    ]);

    // 40 (sin web) + 25 (pocas reseñas) + 15 (rating>=4) + 20 (móvil) = 100
    expect($lead->priority_score)->toBe(100);
});

it('baja la prioridad de un negocio con web y muchas reseñas', function () {
    $user = User::factory()->create();

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Clínica consolidada',
        'google_place_id' => 'prio_002',
        'website'         => 'https://clinica.example',
        'reviews_count'   => 120,
        'rating'          => 4.2,
        'phone'           => '954123123', // fijo
    ]);

    // 0 (con web) + 0 (muchas reseñas) + 15 (rating>=4) + 10 (fijo) = 25
    expect($lead->priority_score)->toBe(25);
});

// ── MRR / valor mensual de la suscripción ──────────────────────────────────

it('el valor mensual usa la cuota pactada por encima de la cuota base del plan', function () {
    $user = User::factory()->create();

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Cliente MRR',
        'google_place_id' => 'mrr_001',
    ]);

    $service = Service::create([
        'user_id'    => $user->id,
        'name'       => 'Web + SEO Local',
        'base_price' => 149.00,
        'is_active'  => true,
    ]);

    $sub = LeadService::create([
        'lead_id'       => $lead->id,
        'service_id'    => $service->id,
        'status'        => 'sold',
        'monthly_price' => 129.00, // descuento pactado
    ]);

    expect($sub->monthlyValue())->toBe(129.0)
        ->and(LeadService::active()->count())->toBe(1);
});

// ── Churn: conservar ingresos históricos ───────────────────────────────────

it('al dar de baja NO borra los ingresos ya cobrados y registra la fecha de baja', function () {
    $user = User::factory()->create();

    $lead = Lead::create([
        'user_id'         => $user->id,
        'business_name'   => 'Cliente que se da de baja',
        'google_place_id' => 'churn_001',
    ]);

    $service = Service::create([
        'user_id'    => $user->id,
        'name'       => 'Presencia Local',
        'base_price' => 99.00,
        'is_active'  => true,
    ]);

    // Alta como cliente activo → se crea el ingreso automático.
    $sub = LeadService::create([
        'lead_id'    => $lead->id,
        'service_id' => $service->id,
        'status'     => 'sold',
    ]);

    expect(Transaction::withoutGlobalScopes()->where('lead_service_id', $sub->id)->count())->toBe(1);

    // Baja real (churned) → el ingreso histórico se conserva.
    $sub->update(['status' => 'churned']);

    expect(Transaction::withoutGlobalScopes()->where('lead_service_id', $sub->id)->count())->toBe(1)
        ->and($sub->fresh()->canceled_at)->not->toBeNull();
});
