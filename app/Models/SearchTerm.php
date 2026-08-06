<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;

class SearchTerm extends Model
{
    public $timestamps = false;

    protected $fillable = ['term', 'location_id', 'created_at'];

    /**
     * Registra un término tecleado, normalizado. $locationId null = búsqueda sin
     * resultados. Términos de menos de 3 caracteres se ignoran (ruido de tecleo).
     */
    public static function log(string $term, ?int $locationId): void
    {
        $term = mb_strtolower(trim($term));

        if (mb_strlen($term) < 3) {
            return;
        }

        // Anti-spam de misses: mismo término 1 vez por hora por IP, y máximo
        // 15 términos fallidos distintos por hora por IP (iterar inventos no llena la tabla).
        if ($locationId === null) {
            $ipKey = 'search-miss:'.request()->ip();
            $termKey = $ipKey.':'.$term;

            if (RateLimiter::tooManyAttempts($termKey, 1) || RateLimiter::tooManyAttempts($ipKey, 15)) {
                return;
            }
            RateLimiter::hit($termKey, 3600);
            RateLimiter::hit($ipKey, 3600);
        }

        self::create(['term' => $term, 'location_id' => $locationId, 'created_at' => now()]);
    }
}
