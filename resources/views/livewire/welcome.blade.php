<div class="min-h-dvh flex flex-col items-center justify-center px-4 py-10 sm:py-16">
    <button
        type="button"
        onclick="UbicaTecTheme.toggle()"
        aria-label="Cambiar tema"
        class="btn btn-circle btn-ghost fixed top-4 left-4 z-50 min-h-[44px] min-w-[44px]"
    >
        <x-mary-icon name="o-moon" class="w-6 h-6 icon-theme-moon" />
        <x-mary-icon name="o-sun" class="w-6 h-6 icon-theme-sun" />
    </button>

    <a
        href="{{ route('map') }}"
        class="btn btn-circle btn-ghost fixed top-4 right-4 z-50 min-h-[44px] min-w-[44px]"
        aria-label="Cerrar y ver el mapa"
    >
        <x-mary-icon name="o-x-mark" class="w-6 h-6" />
    </a>

    <div class="w-full max-w-md flex flex-col items-center gap-6">
        <img
            src="{{ asset('images/locations/logo_itcj.png') }}"
            alt="Instituto Tecnológico de Ciudad Juárez"
            class="w-24 h-24 sm:w-28 sm:h-28 object-contain"
        >

        <h1 class="text-3xl sm:text-4xl font-extrabold text-center leading-tight">
            ¿A dónde vamos a ir?
        </h1>

        {{-- Buscador --}}
        <div class="w-full relative" wire:key="buscador">
            <x-mary-input
                wire:model.live.debounce.300ms="search"
                placeholder="Busca un lugar… (ej. gimnasio, biblioteca, ISC)"
                icon="o-magnifying-glass"
                autofocus
                autocomplete="off"
                class="input-lg text-base"
            />

            @if (trim($search) !== '')
                <div
                    class="absolute inset-x-0 top-full z-20 mt-2 max-h-80 overflow-y-auto rounded-box border border-base-300 bg-base-100 shadow-xl"
                    wire:key="sugerencias"
                >
                    @forelse ($this->suggestions as $location)
                        <button
                            type="button"
                            wire:click="go({{ $location->id }})"
                            wire:key="sug-{{ $location->id }}"
                            class="flex w-full items-center justify-between gap-3 border-b border-base-200 px-4 py-3 text-left last:border-0 hover:bg-base-200 active:bg-base-300"
                        >
                            <span class="font-medium">{{ $location->name }}</span>
                            <span class="badge badge-ghost badge-sm shrink-0">
                                {{ $location->floor === 0 ? 'Exterior' : 'Piso '.$location->floor }}
                            </span>
                        </button>
                    @empty
                        <div class="px-4 py-4 text-sm opacity-60">
                            Sin resultados para "{{ $search }}"
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        {{-- Los más buscados --}}
        <div class="w-full mt-6">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide opacity-60">
                Los más buscados
            </h2>

            <div class="grid grid-cols-3 gap-3">
                @foreach ($this->topLocations as $location)
                    <button
                        type="button"
                        wire:click="go({{ $location->id }})"
                        wire:key="top-{{ $location->id }}"
                        class="card overflow-hidden border border-base-200 bg-base-100 text-left shadow-md transition hover:shadow-lg active:scale-[0.98]"
                    >
                        @if ($location->image)
                            <figure class="h-16 sm:h-24 w-full overflow-hidden bg-base-200">
                                <img
                                    src="{{ asset($location->image) }}"
                                    alt="{{ $location->name }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                >
                            </figure>
                        @endif
                        <div class="card-body p-2 sm:p-3">
                            <p class="text-xs sm:text-sm font-medium leading-snug">
                                {{ $location->name }}
                            </p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
