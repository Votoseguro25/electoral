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
        Schema::create('reportare14', function (Blueprint $table) {
            $table->id('ID'); // Primary key is ID (uppercase in SQL but laravel typically uses lowercase, leaving as ID to match SQL dump if sensitive, but mapped to id() bigIncrements usually. SQL says `ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT)
            $table->text('E14_ID');
            $table->foreignId('DEPARTAMENTO')->constrained('departamentos');
            $table->foreignId('MUNICIPIO')->constrained('municipios');
            $table->foreignId('PUESTO')->constrained('puestos');
            $table->string('MESA', 8);
            $table->integer('TOTAL_VOTANTES-E11');
            $table->integer('TOTAL_VOTOS-URNA');
            $table->integer('VOTOS_BLANCO');
            $table->integer('VOTOS_NULOS');
            $table->integer('SUMA_VOTOS-E14');
            $table->text('OBSERVACION');
            $table->text('ARCHIVO');
            $table->foreignId('TESTIGO')->constrained('users');

            // Note: Timestamps are not present in the SQL dump for this table
        });

        Schema::create('reporte_votos_camara', function (Blueprint $table) {
            $table->id('ReportCam_ID');
            $table->foreignId('Partido')->constrained('partido');
            $table->foreignId('e14')->constrained('reportare14', 'ID');
            $table->bigInteger('votos');
        });

        Schema::create('Votos_candidatosCam', function (Blueprint $table) {
            $table->id('ID');
            $table->foreignId('candidato')->constrained('candidatos');
            $table->foreignId('e14')->constrained('reportare14', 'ID');
            $table->unsignedBigInteger('votos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Votos_candidatosCam');
        Schema::dropIfExists('reporte_votos_camara');
        Schema::dropIfExists('reportare14');
    }
};
