<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // El buscador usa unaccent() en sus ILIKE; en dev se creó a mano — esto
    // cubre BDs nuevas (p. ej. la de tests).
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');
    }

    public function down(): void
    {
        // No se borra: otras BDs/consultas podrían usarla.
    }
};
