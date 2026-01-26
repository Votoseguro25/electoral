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
        Schema::table('personas', function (Blueprint $table) {
            $table->boolean('reporte_voto')->default(false)->after('genero_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('reporte_voto');
        });
    }
};
