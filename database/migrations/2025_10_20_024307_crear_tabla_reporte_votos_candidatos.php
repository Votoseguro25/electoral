<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('reporte_votos_candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testigo_id')->constrained('testigo')->onDelete('cascade');
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('no action');
            $table->integer('votos')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('reporte_votos_candidatos');
    }
};
