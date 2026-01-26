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
        if (!Schema::hasTable('campana')) {
            Schema::create('campana', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tipo_eleccion_id')->constrained('tipo_eleccion')->onUpdate('cascade');
                $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onUpdate('cascade');
                $table->foreignId('candidato_id')->constrained('candidatos')->onUpdate('cascade')->onDelete('cascade');
                $table->foreignId('departamento_id')->constrained('departamentos')->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campana');
    }
};
