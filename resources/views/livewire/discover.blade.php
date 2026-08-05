<div
    class="min-h-dvh px-4 py-16 sm:py-20"
    x-data="{ goingTo: '' }"
>
    {{-- Volver: izquierda = salir de la pantalla --}}
    <a
        href="{{ $category ? route('discover') : route('home') }}"
        class="btn btn-circle btn-ghost fixed top-4 left-4 z-50 min-h-[44px] min-w-[44px]"
        aria-label="{{ $category ? 'Volver a Descubrir' : 'Volver al inicio' }}"
    >
        <x-mary-icon name="o-arrow-left" class="w-6 h-6" />
    </a>

    {{-- Tema: derecha --}}
    <button
        type="button"
        onclick="UbicaTecTheme.toggle()"
        aria-label="Cambiar tema"
        class="btn btn-circle btn-ghost fixed top-4 right-4 z-50 min-h-[44px] min-w-[44px]"
    >
        <x-mary-icon name="o-moon" class="w-6 h-6 icon-theme-moon" />
        <x-mary-icon name="o-sun" class="w-6 h-6 icon-theme-sun" />
    </button>

    <div class="mx-auto w-full max-w-4xl">
        @if ($category)
            {{-- Vista de categoría --}}
            <h1 class="mb-6 flex items-center gap-3 text-2xl sm:text-3xl font-extrabold leading-tight">
                @if ($category->icon)
                    <span aria-hidden="true">{{ $category->icon }}</span>
                @endif
                <span>{{ $category->name }}</span>
            </h1>

            @if ($locations->isEmpty())
                <p class="py-10 text-center text-sm opacity-70">
                    Aún no hay lugares en esta categoría.
                </p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($locations as $location)
                        <button
                            type="button"
                            x-on:click="goingTo = @js($location->name)"
                            wire:click="go({{ $location->id }})"
                            wire:key="loc-{{ $location->id }}"
                            class="card overflow-hidden border border-base-200 bg-base-100 text-left shadow-md transition hover:shadow-lg active:scale-[0.98]"
                        >
                            @if ($location->image)
                                <figure class="h-24 w-full overflow-hidden bg-base-200">
                                    <img
                                        src="{{ asset($location->image) }}"
                                        alt="{{ $location->name }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </figure>
                            @endif
                            <div class="card-body flex-row items-center justify-between gap-2 p-3">
                                <p class="text-sm font-medium leading-snug">
                                    {{ $location->name }}
                                </p>
                                <span class="badge badge-ghost badge-sm shrink-0">
                                    {{ $location->floor === 0 ? 'Exterior' : $location->floor.'º' }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>

                @if ($locations->hasPages())
                    <div class="mt-6">
                        {{ $locations->links() }}
                    </div>
                @endif
            @endif
        @else
            {{-- Índice: todas las categorías --}}
            <h1 class="mb-6 text-2xl sm:text-3xl font-extrabold leading-tight">
                Descubrir en el Tec
            </h1>

            @if ($this->categories->isEmpty())
                <p class="py-10 text-center text-sm opacity-70">
                    Todavía no hay categorías disponibles.
                </p>
            @else
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->categories as $cat)
                        <a
                            href="{{ route('discover', $cat->slug) }}"
                            wire:key="cat-{{ $cat->id }}"
                            class="btn btn-outline gap-2 min-h-[44px] normal-case"
                        >
                            @if ($cat->icon)
                                <span aria-hidden="true">{{ $cat->icon }}</span>
                            @endif
                            <span>{{ $cat->name }}</span>
                            <span class="badge badge-sm badge-neutral">{{ $cat->locations_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- Feedback al navegar: "Llevándote a…" --}}
    <div
        x-show="goingTo"
        x-cloak
        class="fixed inset-0 z-[1200] flex flex-col items-center justify-center gap-4 bg-base-100/80 backdrop-blur"
        role="status"
        aria-live="polite"
    >
        <span class="loading loading-spinner loading-lg text-primary"></span>
        <p class="text-base-content text-lg font-medium text-center px-6">
            Llevándote a <span x-text="goingTo"></span>…
        </p>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
