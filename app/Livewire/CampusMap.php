<?php

namespace App\Livewire;

use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
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

        return Location::search($term)->limit(8)->get();
    }

    /**
     * Al elegir una sugerencia: registra la búsqueda y navega con recarga
     * completa a /mapa/{slug} (sin SPA / wire:navigate).
     */
    public function chooseLocation(int $id)
    {
        $location = Location::findOrFail($id);
        $location->registerSearchHit();

        return redirect()->route('map', $location->slug);
    }

    /**
     * Payload serializable que el JS inline del blade consume vía @json().
     */
    protected function mapPayload(): array
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
            'palette' => self::KIND_COLORS,
            'campusCenter' => self::CAMPUS_CENTER,
        ];
    }

    public function render()
    {
        return view('livewire.campus-map', [
            'mapPayload' => $this->mapPayload(),
        ]);
    }
}
