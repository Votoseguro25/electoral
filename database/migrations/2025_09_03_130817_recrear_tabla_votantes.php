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
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign('votantes_compromiso_id_foreign');
            $table->dropColumn('compromiso_id');
            $table->dropColumn('recomendacion');
        });

        Schema::create('votantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->onDelete('cascade');
            $table->foreignId('compromiso_id')->nullable(true)->constrained('compromisos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('personas', function (Blueprint $table) {
            $table->foreignId('compromiso_id')->nullable(true)->constrained('compromisos')->onDelete('set null');
            $table->string('recomendacion')->nullable(true);
        });

        Schema::dropIfExists('votantes');
    }
};
