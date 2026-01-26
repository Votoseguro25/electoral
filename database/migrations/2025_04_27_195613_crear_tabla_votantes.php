<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('votantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable(false);
            $table->string('cedula')->unique()->nullable(false);
            $table->string('telefono')->nullable(false);
            $table->foreignId('corregimiento_id')->nullable(true)->constrained('corregimientos')->onDelete('set null');
            $table->foreignId('barrio_id')->nullable(true)->constrained('barrios')->onDelete('set null');
            $table->foreignId('mesa_id')->nullable(true)->constrained('mesas')->onDelete('set null');
            $table->foreignId('compromiso_id')->nullable(true)->constrained('compromisos')->onDelete('set null');
            $table->string('recomendacion')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('votantes');
    }
};
