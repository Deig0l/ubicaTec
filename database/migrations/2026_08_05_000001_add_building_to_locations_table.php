<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // Solo lectura: la fuente de verdad es properties.building en geo/piso{1,2}.json;
            // el sync la puebla, el admin solo la muestra/filtra.
            $table->string('building')->nullable()->after('floor');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('building');
        });
    }
};
