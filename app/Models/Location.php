<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'floor',
        'building',
        'kind',
        'category_id',
        'lat',
        'lng',
        'image',
        'phone',
        'email',
        'website',
        'facebook',
        'search_count',
        'is_searchable',
    ];

    protected function casts(): array
    {
        return [
            'floor' => 'integer',
            'kind' => 'integer',
            'category_id' => 'integer',
            'lat' => 'float',
            'lng' => 'float',
            'search_count' => 'integer',
            'is_searchable' => 'boolean',
        ];
    }

    /**
     * Sinónimos de búsqueda de esta locación.
     *
     * @return HasMany<LocationSynonym, $this>
     */
    public function synonyms(): HasMany
    {
        return $this->hasMany(LocationSynonym::class);
    }

    /**
     * Categoría a la que pertenece esta locación.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Busca locaciones cuyo nombre o algún sinónimo coincida con $term,
     * ignorando acentos y mayúsculas/minúsculas (unaccent + ILIKE).
     * Ordena primero por coincidencia de prefijo exacto, luego por
     * search_count descendente.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%'.$term.'%';
        $prefix = $term.'%';

        return $query
            ->where('is_searchable', true)
            ->where(function (Builder $query) use ($like) {
                $query->whereRaw('unaccent(name) ILIKE unaccent(?)', [$like])
                    ->orWhereHas('synonyms', function (Builder $query) use ($like) {
                        $query->whereRaw('unaccent(name) ILIKE unaccent(?)', [$like]);
                    });
            })
            ->orderByRaw('CASE WHEN unaccent(name) ILIKE unaccent(?) THEN 0 ELSE 1 END', [$prefix])
            ->orderByDesc('search_count');
    }

    /**
     * Registra un acierto de búsqueda: contador histórico + fila con timestamp
     * para las estadísticas por hora/día/mes. Si vino de un término tecleado,
     * también lo guarda ligado a esta locación.
     */
    public function registerSearchHit(?string $term = null): void
    {
        // Anti-spam: 1 hit por IP+locación cada 10 min; repetir la misma búsqueda
        // no infla las estadísticas. Trade-off: IPs compartidas (NAT) subcuentan.
        $key = 'search-hit:'.request()->ip().':'.$this->id;

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return;
        }
        RateLimiter::hit($key, 600);

        $this->increment('search_count');
        SearchHit::create(['location_id' => $this->id, 'created_at' => now()]);

        if ($term !== null) {
            SearchTerm::log($term, $this->id);
        }
    }
}
