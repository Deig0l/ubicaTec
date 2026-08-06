<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Qué teclea la gente: location_id NULL = término sin resultados (miss);
        // con location_id = término con el que se llegó a esa locación.
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term')->index();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_terms');
    }
};
