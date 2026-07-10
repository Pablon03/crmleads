<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Los 3 planes de suscripción mensual del negocio (catálogo compartido).
 * base_price = cuota mensual. is_recurring = true.
 * Idempotente: no duplica un plan que ya exista por nombre.
 */
class ServiceSeeder extends Seeder
{
    public static array $plans = [
        [
            'name'         => 'Presencia Local',
            'base_price'   => 99.00,
            'description'  => 'Ficha de Google optimizada, landing sencilla adaptada a móvil, botones de llamada y WhatsApp, solicitud de reseñas e informe mensual.',
        ],
        [
            'name'         => 'Web + SEO Local',
            'base_price'   => 149.00,
            'description'  => 'Todo lo de Presencia Local + web profesional de varias secciones, SEO local, publicaciones en Google Business y revisión mensual de métricas.',
        ],
        [
            'name'         => 'Crecimiento Local',
            'base_price'   => 299.00,
            'description'  => 'Todo lo de Web + SEO Local + estrategia mensual de visibilidad, contenidos extra, campañas básicas y seguimiento cercano con informe ampliado.',
        ],
    ];

    public function run(): void
    {
        $primaryId = User::withoutGlobalScopes()->where('is_admin', true)->min('id')
            ?? User::withoutGlobalScopes()->min('id');

        if ($primaryId) {
            self::ensurePlans($primaryId);
        }
    }

    public static function ensurePlans(int $userId): void
    {
        foreach (self::$plans as $plan) {
            $exists = Service::withoutGlobalScopes()->where('name', $plan['name'])->exists();

            if (! $exists) {
                Service::create(array_merge($plan, [
                    'user_id'      => $userId,
                    'is_recurring' => true,
                    'is_active'    => true,
                ]));
            }
        }
    }
}
