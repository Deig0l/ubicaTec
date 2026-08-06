<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Locaciones · UbicaTec Admin')]
class LocationList extends Component
{
    use Toast;
    use WithPagination;

    public string $search = '';

    public string $building = '';

    public array $sortBy = ['column' => 'search_count', 'direction' => 'desc'];

    /**
     * Columnas ordenables permitidas (evita inyectar cualquier columna vía JS).
     */
    private const SORTABLE_COLUMNS = ['name', 'search_count'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBuilding(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Nombre', 'sortable' => true],
            ['key' => 'building', 'label' => 'Edificio', 'sortable' => false, 'class' => 'w-44'],
            ['key' => 'floor', 'label' => 'Piso', 'sortable' => false, 'class' => 'w-28'],
            ['key' => 'category', 'label' => 'Categoría', 'sortable' => false, 'class' => 'w-40'],
            ['key' => 'synonyms', 'label' => 'Sinónimos', 'sortable' => false],
            ['key' => 'search_count', 'label' => 'Veces buscada', 'sortable' => true, 'class' => 'w-36'],
            ['key' => 'is_searchable', 'label' => 'Buscable', 'sortable' => false, 'class' => 'w-24'],
        ];
    }

    public function floorLabel(int $floor): string
    {
        return match ($floor) {
            1 => '1er piso',
            2 => '2do piso',
            default => 'Exterior / PB',
        };
    }

    public function toggleSearchable(Location $location): void
    {
        $location->update(['is_searchable' => ! $location->is_searchable]);
    }

    public function resetCount(Location $location): void
    {
        $location->update(['search_count' => 0]);

        $this->success("Contador de \"{$location->name}\" reiniciado.");
    }

    public function delete(Location $location): void
    {
        $name = $location->name;
        $location->delete();

        $this->success("\"{$name}\" fue eliminada.");
    }

    private function locations(): LengthAwarePaginator
    {
        $column = in_array($this->sortBy['column'] ?? '', self::SORTABLE_COLUMNS, true)
            ? $this->sortBy['column']
            : 'search_count';

        $direction = ($this->sortBy['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return Location::query()
            ->with('synonyms', 'category')
            ->when($this->building !== '', fn ($query) => $query->where('building', $this->building))
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(function ($q) use ($term) {
                    $q->whereRaw('unaccent(name) ILIKE unaccent(?)', [$term])
                        ->orWhereHas('synonyms', function ($q2) use ($term) {
                            $q2->whereRaw('unaccent(name) ILIKE unaccent(?)', [$term]);
                        });
                });
            })
            ->orderBy($column, $direction)
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.location-list', [
            'headers' => $this->headers(),
            'locations' => $this->locations(),
            'total' => Location::count(),
            'top' => Location::orderByDesc('search_count')->limit(3)->get(),
            'buildings' => Location::whereNotNull('building')->distinct()->orderBy('building')
                ->pluck('building')
                ->map(fn ($b) => ['id' => $b, 'name' => $b]),
        ]);
    }
}
