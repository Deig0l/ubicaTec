<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Descubrir en el Tec · UbicaTec')]
class Discover extends Component
{
    use WithPagination;

    public ?Category $category = null;

    public function mount(?Category $category = null): void
    {
        $this->category = $category?->exists ? $category : null;
    }

    /**
     * Todas las categorías con locaciones, para las chips del índice.
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
     * El usuario eligió una locación: registra la búsqueda y lo manda al mapa.
     */
    public function go(int $id)
    {
        $location = Location::findOrFail($id);
        $location->registerSearchHit();

        return redirect()->route('map', $location->slug);
    }

    public function render()
    {
        $locations = null;

        if ($this->category) {
            $locations = $this->category->locations()
                ->where('is_searchable', true)
                ->orderBy('name')
                ->paginate(24);
        }

        return view('livewire.discover', [
            'locations' => $locations,
        ]);
    }
}
