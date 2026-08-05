<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /** Coordenadas del centro del campus, usadas como placeholder cuando no hay match en piso0.json. */
    private const CAMPUS_LAT = 31.719091;

    private const CAMPUS_LNG = -106.422;

    /** Kinds permitidos para crear locaciones de espacios interiores (ver legacy/Guia-GeoJSON.txt). */
    private const ALLOWED_KINDS = [0, 3, 6, 11, 12, 13, 15];

    /** Prefijos de nombres genéricos a saltar (ya normalizados: sin acentos, minúsculas). */
    private const GENERIC_STEMS = ['banos', 'escalera', 'acceso', 'pasillo', 'bodega', 'cubiculo', 'puerta'];

    /** Nombres normalizados ya usados como location o sinónimo, para evitar duplicados. */
    private array $usedNames = [];

    /** Slugs ya asignados, para evitar colisiones. */
    private array $usedSlugs = [];

    public function run(): void
    {
        $this->seedAdmin();

        $piso0 = $this->loadGeoJson(0);
        $piso1 = $this->loadGeoJson(1);
        $piso2 = $this->loadGeoJson(2);

        $this->seedMainLocations($piso0);
        $this->seedInteriorSpaces(0, $piso0);
        $this->seedInteriorSpaces(1, $piso1);
        $this->seedInteriorSpaces(2, $piso2);
    }

    private function seedAdmin(): void
    {
        // En producción define UBICATEC_ADMIN_PASSWORD en .env; el valor por
        // defecto solo es para desarrollo local.
        User::updateOrCreate(
            ['email' => 'admin@ubicatec.test'],
            [
                'name' => 'Administrador UbicaTec',
                'password' => Hash::make(env('UBICATEC_ADMIN_PASSWORD', 'ubicatec2026')),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function loadGeoJson(int $n): array
    {
        $path = public_path("geo/piso{$n}.json");

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<string, mixed>  $piso0
     */
    private function seedMainLocations(array $piso0): void
    {
        $mains = [
            [
                'name' => 'Ciencias Básicas',
                'image' => 'ciencias-basicas.jpg',
                'description' => 'Edificio de Ciencias Básicas, donde se imparten las materias comunes a todas las carreras del instituto.',
                'synonyms' => [],
            ],
            [
                'name' => 'Económico Administrativo',
                'image' => 'eco-admin.jpg',
                'description' => 'Edificio de las carreras del área económico-administrativa, como Gestión Empresarial y Contaduría.',
                'synonyms' => [],
            ],
            [
                'name' => 'Eléctrica y Electrónica',
                'image' => 'electro.jpg',
                'description' => 'Edificio de las carreras de Ingeniería Eléctrica e Ingeniería Electrónica.',
                'synonyms' => [],
            ],
            [
                'name' => 'Industrial y Logística',
                'image' => 'industrial.jpg',
                'description' => 'Edificio de las carreras de Ingeniería Industrial e Ingeniería en Logística.',
                'synonyms' => [],
            ],
            [
                'name' => 'Metal Mecánica',
                'image' => 'metalMecanica.jpg',
                'description' => 'Edificio de la carrera de Ingeniería en Manufactura, con talleres de metal mecánica.',
                'synonyms' => [],
            ],
            [
                'name' => 'Sistemas Computacionales',
                'image' => 'sistemas.jpg',
                'description' => 'Edificio de la carrera de Ingeniería en Sistemas Computacionales, con laboratorios de cómputo.',
                'synonyms' => ['ISC', 'sistemas', 'cómputo'],
            ],
            [
                'name' => 'Alberca',
                'image' => 'Alberca.jpg',
                'description' => 'Alberca del instituto, usada para clases de natación y actividades deportivas.',
                'synonyms' => ['piscina'],
            ],
            [
                'name' => 'Cafetería',
                'image' => 'Cafeteria.jpg',
                'description' => 'Cafetería principal del instituto, donde los estudiantes pueden comer entre clases.',
                'synonyms' => ['comedor'],
                'search_count' => 4,
            ],
            [
                'name' => 'Centro de Información (Biblioteca)',
                'image' => 'Biblioteca.jpg',
                'description' => 'Biblioteca del instituto, con acervo bibliográfico, salas de estudio y equipo de cómputo.',
                'synonyms' => ['biblioteca', 'CI'],
                'search_count' => 5,
            ],
            [
                'name' => 'Coffee Shop',
                'image' => 'Coffee-Shop.jpg',
                'description' => 'Cafetería pequeña donde se ofrecen bebidas y snacks rápidos.',
                'synonyms' => ['café'],
            ],
            [
                'name' => 'Consultorio Médico',
                'image' => 'consultorio-medico.jpg',
                'description' => 'Consultorio médico y de enfermería para atención de primeros auxilios a la comunidad estudiantil.',
                'synonyms' => ['doctor', 'enfermería'],
            ],
            [
                'name' => 'Gimnasio',
                'image' => 'gym.jpg',
                'description' => 'Gimnasio del instituto, usado para clases de educación física y eventos deportivos.',
                'synonyms' => ['gym'],
                'search_count' => 3,
            ],
            [
                'name' => 'Liebre Shop',
                'image' => 'liebre-shop.jpg',
                'description' => 'Tienda escolar donde se venden artículos, uniformes y papelería del instituto.',
                'synonyms' => [],
            ],
            [
                'name' => 'Sala de Alumnos',
                'image' => 'sala-alumnos.jpg',
                'description' => 'Espacio de descanso y convivencia para los estudiantes entre clases.',
                'synonyms' => [],
            ],
        ];

        foreach ($mains as $data) {
            $centroid = $this->findCentroid($data['name'], $piso0);

            $location = Location::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'],
                'floor' => 0,
                'kind' => 0,
                'lat' => $centroid['lat'] ?? self::CAMPUS_LAT,
                'lng' => $centroid['lng'] ?? self::CAMPUS_LNG,
                'image' => 'images/locations/'.$data['image'],
                'search_count' => $data['search_count'] ?? 0,
                'is_searchable' => true,
            ]);

            foreach ($data['synonyms'] as $synonym) {
                $location->synonyms()->create(['name' => $synonym]);
                $this->markNameUsed($synonym);
            }

            $this->markNameUsed($data['name']);
        }
    }

    /**
     * Crea locaciones para espacios interiores de un piso (kind permitido, nombre no genérico
     * y no duplicado de una location o sinónimo ya existente).
     *
     * @param  array<string, mixed>  $geojson
     */
    private function seedInteriorSpaces(int $floor, array $geojson): void
    {
        foreach ($this->flattenFeatures($geojson) as $feature) {
            $properties = $feature['properties'] ?? [];
            $name = trim((string) ($properties['name'] ?? ''));
            $kind = $properties['kind'] ?? null;

            if ($name === '' || $kind === null) {
                continue;
            }

            if (! in_array($kind, self::ALLOWED_KINDS, true)) {
                continue;
            }

            if ($this->isGenericName($name)) {
                continue;
            }

            // Los GeoJSON traen salones con solo el número ("126"); sin prefijo
            // ensucian la búsqueda y los recientes.
            if ($kind === 3 && preg_match('/^\d+$/', $name)) {
                $name = 'Salón '.$name;
            }

            if ($this->isDuplicateName($name)) {
                continue;
            }

            $centroid = $this->centroidOfGeometry($feature['geometry'] ?? null);

            if ($centroid === null) {
                continue;
            }

            Location::create([
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'description' => null,
                'floor' => $floor,
                'kind' => $kind,
                'lat' => $centroid['lat'],
                'lng' => $centroid['lng'],
                'image' => null,
                'search_count' => 0,
                'is_searchable' => true,
            ]);

            $this->markNameUsed($name);
        }
    }

    /**
     * Recorre recursivamente el árbol de features, aplanando FeatureCollections
     * anidadas (presentes en piso1.js por datos legacy inconsistentes).
     *
     * @param  array<string, mixed>  $collection
     * @return Generator<array<string, mixed>>
     */
    private function flattenFeatures(array $collection): Generator
    {
        foreach ($collection['features'] ?? [] as $feature) {
            if (($feature['type'] ?? null) === 'FeatureCollection') {
                yield from $this->flattenFeatures($feature);

                continue;
            }

            yield $feature;
        }
    }

    /**
     * Busca en el geojson dado el feature cuyo nombre mejor coincide (parcial, sin acentos)
     * con $name y devuelve el centroide de su polígono, o null si no hay coincidencia.
     *
     * @param  array<string, mixed>  $geojson
     * @return array{lat: float, lng: float}|null
     */
    private function findCentroid(string $name, array $geojson): ?array
    {
        $best = null;
        $bestScore = 0;

        foreach ($this->flattenFeatures($geojson) as $feature) {
            $featureName = trim((string) ($feature['properties']['name'] ?? ''));

            if ($featureName === '') {
                continue;
            }

            $score = $this->matchScore($name, $featureName);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $feature;
            }
        }

        if ($best === null) {
            return null;
        }

        return $this->centroidOfGeometry($best['geometry'] ?? null);
    }

    /**
     * Puntúa qué tan bien coinciden dos nombres (sin acentos, minúsculas):
     * 1000 = idénticos, 500+ = uno contiene al otro, <500 = comparten una palabra
     * significativa (>=5 caracteres), 0 = sin relación.
     */
    private function matchScore(string $a, string $b): int
    {
        $na = $this->normalize($a);
        $nb = $this->normalize($b);

        if ($na === '' || $nb === '') {
            return 0;
        }

        if ($na === $nb) {
            return 1000;
        }

        if (str_contains($na, $nb) || str_contains($nb, $na)) {
            return 500 + min(strlen($na), strlen($nb));
        }

        $wordsA = array_filter(explode(' ', $na), fn ($w) => strlen($w) >= 5);
        $wordsB = array_filter(explode(' ', $nb), fn ($w) => strlen($w) >= 5);

        $best = 0;

        foreach ($wordsA as $wa) {
            foreach ($wordsB as $wb) {
                if ($wa === $wb) {
                    $best = max($best, strlen($wa));
                }
            }
        }

        return $best;
    }

    /**
     * Calcula el centroide (promedio) del primer ring del polígono/multipolígono.
     * El GeoJSON guarda las coordenadas como [lng, lat].
     *
     * @param  array<string, mixed>|null  $geometry
     * @return array{lat: float, lng: float}|null
     */
    private function centroidOfGeometry(?array $geometry): ?array
    {
        if (! $geometry) {
            return null;
        }

        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? null;

        $ring = match ($type) {
            'Polygon' => $coordinates[0] ?? null,
            'MultiPolygon' => $coordinates[0][0] ?? null,
            default => null,
        };

        if (! is_array($ring) || $ring === []) {
            return null;
        }

        $sumLat = 0.0;
        $sumLng = 0.0;
        $count = 0;

        foreach ($ring as $point) {
            if (! isset($point[0], $point[1])) {
                continue;
            }

            $sumLng += (float) $point[0];
            $sumLat += (float) $point[1];
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return [
            'lat' => $sumLat / $count,
            'lng' => $sumLng / $count,
        ];
    }

    /**
     * Normaliza un nombre para comparación: sin acentos, minúsculas, solo alfanumérico
     * y espacios simples.
     */
    private function normalize(string $value): string
    {
        $ascii = Str::ascii($value);
        $lower = mb_strtolower($ascii);
        $clean = preg_replace('/[^a-z0-9]+/', ' ', $lower);

        return trim(preg_replace('/\s+/', ' ', (string) $clean));
    }

    /**
     * Indica si $name comienza con alguno de los prefijos genéricos a saltar.
     */
    private function isGenericName(string $name): bool
    {
        $normalized = $this->normalize($name);

        foreach (self::GENERIC_STEMS as $stem) {
            if (str_starts_with($normalized, $stem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Indica si $name (o una porción completa de palabras de él) ya fue usado
     * como nombre de location o sinónimo.
     */
    private function isDuplicateName(string $name): bool
    {
        $normalized = $this->normalize($name);

        foreach ($this->usedNames as $used) {
            if ($this->wordBoundaryContains($normalized, $used)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compara dos strings normalizados verificando contención por palabras completas
     * (evita falsos positivos como "CI" dentro de "Multifuncional").
     */
    private function wordBoundaryContains(string $a, string $b): bool
    {
        $paddedA = " {$a} ";
        $paddedB = " {$b} ";

        return str_contains($paddedA, $paddedB) || str_contains($paddedB, $paddedA);
    }

    private function markNameUsed(string $name): void
    {
        $this->usedNames[] = $this->normalize($name);
    }

    /**
     * Genera un slug único a partir de $name, evitando colisiones dentro de este seeder.
     */
    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lugar';
        $slug = $base;
        $i = 2;

        while (in_array($slug, $this->usedSlugs, true)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $this->usedSlugs[] = $slug;

        return $slug;
    }
}
