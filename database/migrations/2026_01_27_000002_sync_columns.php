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
        Schema::table('personas', function (Blueprint $table) {
            if (!Schema::hasColumn('personas', 'municipio_id')) {
                $table->foreignId('municipio_id')->default(1)->constrained('municipios')->onUpdate('cascade');
            }
        });

        Schema::table('reporte_votos_candidatos', function (Blueprint $table) {
            if (!Schema::hasColumn('reporte_votos_candidatos', 'e14')) {
                // Determine placement if important, but usually just adding is enough.
                $table->foreignId('e14')->after('candidato_id')->constrained('reportare14', 'ID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reporte_votos_candidatos', function (Blueprint $table) {
            $table->dropForeign(['e14']);
            $table->dropColumn('e14');
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['municipio_id']);
            $table->dropColumn('municipio_id');
        });
    }
};
