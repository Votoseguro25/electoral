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
            $table->string('altitud')->default('0')->change();
            $table->string('longitud')->default('0')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barrios', function (Blueprint $table) {
            $table->string('altitud')->default(null)->change();
            $table->string('longitud')->default(null)->change();
        });
    }
};
