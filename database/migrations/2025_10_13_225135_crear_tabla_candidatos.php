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
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('foto')->nullable();
            $table->string('color', 7)->nullable();
            $table->foreignId('partido_id')->nullable(true)->constrained('partido')->onDelete('set null');
            $table->foreignId('tipo_candidatura_id')->constrained('tipo_candidatura');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
