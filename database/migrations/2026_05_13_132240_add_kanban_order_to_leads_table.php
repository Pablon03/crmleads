<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Posición decimal para ordenamiento kanban (Flowforge usa decimal-based positioning)
            $table->decimal('kanban_order', 15, 10)->nullable()->after('status_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('kanban_order');
        });
    }
};
