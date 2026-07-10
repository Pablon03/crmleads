<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Convierte el CRM en un modelo de suscripción mensual y de equipo:
 *  - services: distingue servicios recurrentes de pagos únicos y permite cuota de alta.
 *  - lead_service (la "suscripción" de un cliente a un plan): cuota mensual real,
 *    día de cobro, fecha de alta y fecha de baja (churn).
 *  - leads: responsable asignado, puntuación de prioridad y origen del lead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // base_price pasa a interpretarse como CUOTA MENSUAL para servicios recurrentes.
            $table->boolean('is_recurring')->default(true)->after('base_price');
            $table->decimal('setup_fee', 10, 2)->nullable()->after('is_recurring'); // alta / matrícula (pago único)
        });

        Schema::table('lead_service', function (Blueprint $table) {
            $table->decimal('monthly_price', 10, 2)->nullable()->after('sold_price'); // cuota mensual pactada
            $table->unsignedTinyInteger('billing_day')->nullable()->after('monthly_price'); // día del mes de cobro (1-28)
            $table->date('started_at')->nullable()->after('sold_at');   // alta de la suscripción
            $table->date('canceled_at')->nullable()->after('started_at'); // baja (churn)
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('priority_score')->nullable()->after('reviews_count');
            $table->string('source')->nullable()->default('google_maps')->after('priority_score');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn(['priority_score', 'source']);
        });

        Schema::table('lead_service', function (Blueprint $table) {
            $table->dropColumn(['monthly_price', 'billing_day', 'started_at', 'canceled_at']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'setup_fee']);
        });
    }
};
