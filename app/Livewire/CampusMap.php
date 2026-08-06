<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\SearchTerm;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Mapa · UbicaTec')]
class CampusMap extends Component
{
    /**
     * Paleta kind → color de relleno, igual al legacy.
     */
    public const KIND_COLORS = [
        0 => '#800026',
        1 => '#800026',
        2 => '#2cff1b',
        3 => '#f0e1cb',
        4 => '#800026',
        5 => '#800026',
        6 => '#c0392b',
        7 => '#b9b9b9',
        8 => '#0080ff',
        9 => '#ff00ec',
        10 => '#E31A1C',
        11 => '#bb8fce',
        12 => '#FD8D3C',
        13 => '#FEB24C',
        14 => '#FED976',
        15 => '#52be80',
    ];

    /**
     * Centro geográfico del campus (placeholder / vista general).
     */
    public const CAMPUS_CENTER = [31.719091, -106.422];

    public ?Location $location = null;

    public string $search = '';

    public function mount(?Location $location = null): void
    {
        $this->location = $location;
    }

    /**
     * Sugerencias de búsqueda en vivo (máx. 8) para la barra compacta del mapa.
     */
    #[Computed]
    public function suggestions(): Collection
    {
        $term = trim($this->search);

        if ($term === '') {
            return new Collection();
        }

        $results = Location::search($term)->limit(8)->get();

        if ($results->isEmpty()) {
            SearchTerm::log($term, null); // búsqueda sin resultados: oro para sinónimos
        }

        return $results;
    }

    /**
     * Al elegir una sugerencia: registra la búsqueda y navega con recarga
     * completa a /mapa/{slug} (sin SPA / wire:navigate).
     */
    public function chooseLocation(int $id)
    {
        $location = Location::findOrFail($id);
        $location->registerSearchHit($this->search);

        return redirect()->route('map', $location->slug);
    }

    /**
     * Clave de comparación edificio ↔ locación: sin acentos y en minúsculas,
     * porque los `building` del GeoJSON no siempre traen los acentos del nombre real.
     */
    private function buildingKey(string $name): string
    {
        return mb_strtolower(Str::ascii($name));
    }

    /**
     * Espacios interiores (pisos 1-2) agrupados por su edificio normalizado.
     *
     * @return \Illuminate\Support\Collection<string, Collection<int, Location>>
     */
    private function interiorsByBuilding(): \Illuminate\Support\Collection
    {
        return Location::where('floor', '>', 0)
            ->whereNotNull('building')
            ->where('is_searchable', true)
            ->orderBy('floor')
            ->orderBy('name')
            ->get(['name', 'slug', 'floor', 'building'])
            ->groupBy(fn ($l) => $this->buildingKey($l->building));
    }

    /**
     * Payload serializable que el JS inline del blade consume vía @json().
     *
     * @param  \Illuminate\Support\Collection  $interiors  resultado de interiorsByBuilding()
     */
    protected function mapPayload(\Illuminate\Support\Collection $interiors): array
    {
        return [
            'location' => $this->location ? [
                'id' => $this->location->id,
                'name' => $this->location->name,
                'description' => $this->location->description,
                'floor' => $this->location->floor,
                'lat' => $this->location->lat,
                'lng' => $this->location->lng,
                'phone' => $this->location->phone,
                'email' => $this->location->email,
                'website' => $this->location->website,
                'facebook' => $this->location->facebook,
                'image' => $this->location->image ? asset($this->location->image) : null,
            ] : null,
            'geo' => [
                0 => asset('geo/piso0.json'),
                1 => asset('geo/piso1.json'),
                2 => asset('geo/piso2.json'),
            ],
            // Click en un polígono de piso 0 (edificio/cancha/estacionamiento) → sheet
            // deslizante con su info, sin recargar. Empata por nombre exacto de la
            // feature; si el nombre no existe en locations, no es clickeable.
            'floor0Info' => Location::where('floor', 0)
                ->get(['name', 'slug', 'description', 'image', 'phone', 'email', 'website', 'facebook'])
                ->mapWithKeys(fn ($l) => [$l->name => [
                    'name' => $l->name,
                    'url' => route('map', $l->slug),
                    'description' => $l->description,
                    'image' => $l->image ? asset($l->image) : null,
                    'phone' => $l->phone,
                    'email' => $l->email,
                    'website' => $l->website,
                    'facebook' => $l->facebook,
                    'spaces' => ($interiors[$this->buildingKey($l->name)] ?? collect())
                        ->map(fn ($s) => ['name' => $s->name, 'floor' => $s->floor, 'url' => route('map', $s->slug)])
                        ->values(),
                ]]),
            'palette' => self::KIND_COLORS,
            'campusCenter' => self::CAMPUS_CENTER,
        ];
    }

    public function render()
    {
        $interiors = $this->interiorsByBuilding();

        return view('livewire.campus-map', [
            'mapPayload' => $this->mapPayload($interiors),
            'spaces' => $this->location
                ? ($interiors[$this->buildingKey($this->location->name)] ?? collect())
                : collect(),
        ]);
    }
}
