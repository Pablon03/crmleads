<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('status_id')->nullable()->constrained('lead_statuses')->onDelete('set null');

            // Datos del negocio
            $table->string('business_name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('category')->nullable();

            // Métricas de Google Maps
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('reviews_count')->nullable();

            // Identificador único de Google
            $table->string('google_place_id')->nullable();

            // Coordenadas
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Campos JSON (jsonb en Postgres, json en SQLite para tests)
            $table->json('opening_hours')->nullable();
            $table->json('images')->nullable();
            $table->json('raw_data')->nullable(); // payload completo de la API

            // Gestión CRM
            $table->timestamp('follow_up_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // Índice único compuesto para evitar duplicados por usuario
            $table->unique(['user_id', 'google_place_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
