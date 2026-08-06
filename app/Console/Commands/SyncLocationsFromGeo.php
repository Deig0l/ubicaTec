<?php

namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncLocationsFromGeo extends Command
{
    protected $signature = 'ubicatec:sync-locations {--dry : Solo mostrar qué se crearía}';

    protected $description = 'Crea en la tabla locations los espacios del GeoJSON (piso0-2) que aún no existen; de los existentes solo rellena building cuando está vacío.';

    /** kind → category_id (Departamentos=1, Salones=3, Laboratorios=4, Comida=5, Deportes=6, Servicios=7). */
    private const KIND_CATEGORY = [
        3 => 3, 12 => 4, 6 => 1, 5 => 5,
        2 => 7, 4 => 7, 8 => 7, 9 => 7, 10 => 7, 11 => 7, 13 => 7, 14 => 7,
        15 => 6, 7 => 7,
    ];

    /** Circulación que no debe aparecer en el buscador. */
    private const NOT_SEARCHABLE_KINDS = [2, 4, 10];

    /** Nombres ya usados (en minúsculas) — locations.name es UNIQUE. */
    private array $takenNames = [];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $mLat = 110922.0;
        $mLng = 111320.0 * cos(deg2rad(31.719091));

        $this->takenNames = Location::pluck('name')->map(fn ($n) => mb_strtolower($n))->flip()->all();

        $created = 0;
        $matched = 0;
        $omitted = 0;
        $backfilled = 0;

        foreach ([0, 1, 2] as $floor) {
            $geo = json_decode(file_get_contents(public_path("geo/piso{$floor}.json")), true);

            $existing = Location::where('floor', $floor)->get(['id', 'name', 'lat', 'lng', 'building'])
                ->map(fn ($l) => ['id' => $l->id, 'key' => $this->normalize($l->name), 'lat' => $l->lat, 'lng' => $l->lng, 'building' => $l->building])
                ->all();

            foreach ($geo['features'] as $f) {
                $name = trim($f['properties']['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $kind = (int) ($f['properties']['kind'] ?? 0);
                $building = $f['properties']['building'] ?? null;
                [$lng, $lat] = $this->centroid($f['geometry']['coordinates'][0]);

                // Mismo nombre normalizado a menos de 60 m en el mismo piso = ya existe.
                $idx = $this->near($existing, $this->normalize($name), $lat, $lng, 60.0, $mLat, $mLng);
                if ($idx !== null) {
                    $matched++;
                    $match = $existing[$idx];
                    if ($building !== null && $match['id'] !== null && $match['building'] === null) {
                        $backfilled++;
                        $existing[$idx]['building'] = $building; // el primer feature (el que creó el registro) manda
                        if (! $dry) {
                            Location::whereKey($match['id'])->update(['building' => $building]);
                        }
                    }
                    continue;
                }

                $base = preg_match('/^\d+$/', $name) ? "Salón {$name}" : $name;
                $displayName = $this->resolveName($base, $building);
                if ($displayName === null) {
                    $omitted++; // nombre y variante con edificio ya tomados: duplicado real
                    // El registro "base (Edificio)" ya existe; su nombre trae el edificio, rellenar es inequívoco.
                    if ($building !== null && ! $dry) {
                        $backfilled += Location::where('name', "{$base} ({$building})")
                            ->whereNull('building')->update(['building' => $building]);
                    }
                    continue;
                }

                $created++;
                $existing[] = ['id' => null, 'key' => $this->normalize($base), 'lat' => $lat, 'lng' => $lng, 'building' => $building];
                $this->takenNames[mb_strtolower($displayName)] = true;

                if ($dry) {
                    $this->line("  + piso {$floor}: {$displayName}");
                    continue;
                }

                Location::create([
                    'name' => $displayName,
                    'slug' => $this->uniqueSlug($displayName),
                    'floor' => $floor,
                    'building' => $building,
                    'kind' => $kind,
                    'category_id' => self::KIND_CATEGORY[$kind] ?? null,
                    'lat' => round($lat, 7),
                    'lng' => round($lng, 7),
                    'is_searchable' => ! in_array($kind, self::NOT_SEARCHABLE_KINDS, true),
                ]);
            }
        }

        $verb = $dry ? 'se crearían' : 'creadas';
        $this->info("Locaciones {$verb}: {$created} · ya existentes: {$matched} (building rellenado: {$backfilled}) · duplicados omitidos: {$omitted}");

        return self::SUCCESS;
    }

    /** Devuelve un nombre libre: el base, o "base (Edificio)", o null si ambos están tomados. */
    private function resolveName(string $base, ?string $building): ?string
    {
        if (! isset($this->takenNames[mb_strtolower($base)])) {
            return $base;
        }
        if ($building !== null) {
            $candidate = "{$base} ({$building})";
            if (! isset($this->takenNames[mb_strtolower($candidate)])) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Devuelve el índice del pool que coincide (mismo nombre normalizado a menos de $radius m), o null.
     *
     * @param list<array{id: int|null, key: string, lat: float|null, lng: float|null, building: string|null}> $pool
     */
    private function near(array $pool, string $key, float $lat, float $lng, float $radius, float $mLat, float $mLng): ?int
    {
        foreach ($pool as $i => $p) {
            if ($p['key'] !== $key) {
                continue;
            }
            if ($p['lat'] === null || $p['lng'] === null) {
                return $i; // mismo nombre sin pin: lo damos por existente
            }
            if (hypot(($p['lat'] - $lat) * $mLat, ($p['lng'] - $lng) * $mLng) <= $radius) {
                return $i;
            }
        }

        return null;
    }

    private function normalize(string $name): string
    {
        $n = mb_strtolower(trim($name));
        $n = strtr($n, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return preg_replace('/^(salon|aula)\s+/', '', $n) ?? $n;
    }

    /** @param list<list<float>> $ring */
    private function centroid(array $ring): array
    {
        $lng = array_sum(array_column($ring, 0)) / count($ring);
        $lat = array_sum(array_column($ring, 1)) / count($ring);

        return [$lng, $lat];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'espacio';
        $slug = $base;
        for ($i = 2; Location::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
