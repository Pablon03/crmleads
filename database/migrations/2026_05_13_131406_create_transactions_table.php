<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Referencias opcionales
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('lead_service_id')->nullable(); // FK manual, sin constraint para evitar ciclos

            // Datos contables
            $table->string('type'); // income|expense
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('transacted_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
