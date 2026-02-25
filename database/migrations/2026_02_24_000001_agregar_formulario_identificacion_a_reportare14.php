<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reportare14', function (Blueprint $table) {
            $table->text('Formulario_Identificacion')->after('E14_ID');
        });
    }

    public function down(): void
    {
        Schema::table('reportare14', function (Blueprint $table) {
            $table->dropColumn('Formulario_Identificacion');
        });
    }
};
