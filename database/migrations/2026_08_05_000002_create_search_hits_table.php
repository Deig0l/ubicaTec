<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una fila por búsqueda elegida: search_count sigue siendo el total histórico;
        // esto existe para poder cortar por hora/día/mes desde su creación en adelante.
        Schema::create('search_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_hits');
    }
};
