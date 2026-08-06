<div>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Locaciones</h1>
            <p class="text-sm text-base-content/60">Administra los lugares del campus, sus sinónimos y su visibilidad en el buscador.</p>
        </div>

        <x-mary-button label="Nueva locación" icon="o-plus" link="{{ route('admin.locations.create') }}" no-wire-navigate class="btn-primary" />
    </div>

    {{-- Mini stats: total + top 3 más buscadas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-mary-stat title="Total de locaciones" :value="$total" icon="o-building-office-2" color="text-primary" />

        @forelse ($top as $i => $location)
            <x-mary-stat
                :title="'#'.($i + 1).' más buscada'"
                :value="$location->name"
                :description="$location->search_count.' búsquedas'"
                icon="o-fire"
                color="text-secondary"
            />
        @empty
            <div class="col-span-3 flex items-center text-sm text-base-content/50">
                Aún no hay búsquedas registradas.
            </div>
        @endforelse
    </div>

    <div class="mb-4 flex flex-col sm:flex-row gap-3">
        <div class="max-w-sm flex-1">
            <x-mary-input
                wire:model.live.debounce.400ms="search"
                placeholder="Buscar por nombre o sinónimo..."
                icon="o-magnifying-glass"
                clearable
            />
        </div>
        <div class="max-w-xs flex-1">
            <x-mary-select
                wire:model.live="building"
                :options="$buildings"
                placeholder="Todos los edificios"
                placeholder-value=""
                icon="o-building-office"
            />
        </div>
    </div>

    <x-mary-table :headers="$headers" :rows="$locations" :sort-by="$sortBy" with-pagination striped>
        @scope('cell_building', $location)
            @if ($location->building)
                {{ $location->building }}
            @else
                <span class="text-base-content/30 text-xs">—</span>
            @endif
        @endscope

        @scope('cell_floor', $location)
            {{ $this->floorLabel($location->floor) }}
        @endscope

        @scope('cell_category', $location)
            @if ($location->category)
                <span class="badge badge-ghost gap-1">
                    <span>{{ $location->category->icon }}</span>
                    <span>{{ $location->category->name }}</span>
                </span>
            @else
                <span class="text-base-content/30 text-xs">Sin categoría</span>
            @endif
        @endscope

        @scope('cell_synonyms', $location)
            <div class="flex flex-wrap gap-1 max-w-xs">
                @forelse ($location->synonyms as $synonym)
                    <span class="badge badge-sm badge-ghost">{{ $synonym->name }}</span>
                @empty
                    <span class="text-base-content/30 text-xs">—</span>
                @endforelse
            </div>
        @endscope

        @scope('cell_is_searchable', $location)
            <input
                type="checkbox"
                class="toggle toggle-sm toggle-success"
                wire:click="toggleSearchable({{ $location->id }})"
                @checked($location->is_searchable)
            />
        @endscope

        @scope('actions', $location)
            <div class="flex gap-1 justify-end">
                <x-mary-button
                    icon="o-pencil-square"
                    link="{{ route('admin.locations.edit', $location) }}"
                    no-wire-navigate
                    class="btn-ghost btn-sm"
                    tooltip="Editar"
                />

                <x-mary-button
                    icon="o-arrow-path"
                    wire:click="resetCount({{ $location->id }})"
                    wire:confirm="¿Reiniciar el contador de búsquedas de &quot;{{ $location->name }}&quot;?"
                    class="btn-ghost btn-sm"
                    tooltip="Resetear contador"
                />

                <x-mary-button
                    icon="o-trash"
                    wire:click="delete({{ $location->id }})"
                    wire:confirm="¿Eliminar &quot;{{ $location->name }}&quot;? Esta acción no se puede deshacer."
                    class="btn-ghost btn-sm text-error"
                    tooltip="Eliminar"
                />
            </div>
        @endscope

        <x-slot:empty>
            <div class="py-6 text-center text-base-content/50">
                No se encontraron locaciones con ese criterio.
            </div>
        </x-slot:empty>
    </x-mary-table>
</div>
