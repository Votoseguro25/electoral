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
        Schema::table('barrios', function (Blueprint $table) {
            $table->string('altitud')->nullable(false);
            $table->string('longitud')->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('barrios', function (Blueprint $table) {
            $table->dropColumn('altitud');
            $table->dropColumn('longitud');
        });
    }
};
