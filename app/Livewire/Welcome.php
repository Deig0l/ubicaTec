<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('¿A dónde vamos a ir? · UbicaTec')]
class Welcome extends Component
{
    public string $search = '';

    /**
     * Sugerencias de búsqueda en vivo (máx. 8), o vacío si no hay término.
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
     * Top 3 lugares más buscados, para la sección "Los más buscados".
     */
    #[Computed]
    public function topLocations(): Collection
    {
        return Location::query()
            ->where('is_searchable', true)
            ->orderByDesc('search_count')
            ->limit(3)
            ->get();
    }

    /**
     * Categorías con locaciones, para la sección "Descubrir en el Tec".
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->has('locations')
            ->withCount('locations')
            ->orderBy('name')
            ->get();
    }

    /**
     * El usuario eligió un lugar: registra la búsqueda y lo manda al mapa.
     */
    public function go(int $id)
    {
        $location = Location::findOrFail($id);
        $location->registerSearchHit();

        return redirect()->route('map', $location->slug);
    }

    public function render()
    {
        return view('livewire.welcome');
    }
}
