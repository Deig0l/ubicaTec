<div class="flex flex-col bg-base-200" style="height:100vh; height:100dvh;">
    {{-- Barra superior fija --}}
    <div class="z-[1000] flex shrink-0 items-center gap-2 border-b border-base-300 bg-base-100 px-2 py-2 shadow-sm">
        <a
            href="{{ route('home') }}"
            aria-label="Volver al inicio"
            class="btn btn-circle btn-ghost btn-sm sm:btn-md shrink-0"
        >
            <x-mary-icon name="o-arrow-left" class="w-5 h-5" />
        </a>

        <div class="relative min-w-0 flex-1" wire:key="mapa-buscador">
            <x-mary-input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar en el campus…"
                icon="o-magnifying-glass"
                autocomplete="off"
                class="input-sm sm:input-md"
            />

            @if (trim($search) !== '')
                <div
                    class="absolute inset-x-0 top-full z-30 mt-1 max-h-72 overflow-y-auto rounded-box border border-base-300 bg-base-100 shadow-xl"
                    wire:key="mapa-sugerencias"
                >
                    @forelse ($this->suggestions as $suggestion)
                        <button
                            type="button"
                            wire:click="chooseLocation({{ $suggestion->id }})"
                            wire:key="mapa-sug-{{ $suggestion->id }}"
                            class="flex w-full items-center justify-between gap-3 border-b border-base-200 px-4 py-2.5 text-left last:border-0 hover:bg-base-200 active:bg-base-300"
                        >
                            <span class="font-medium text-sm">{{ $suggestion->name }}</span>
                            <span class="badge badge-ghost badge-sm shrink-0">
                                {{ $suggestion->floor === 0 ? 'Exterior' : 'Piso '.$suggestion->floor }}
                            </span>
                        </button>
                    @empty
                        <div class="px-4 py-3 text-sm opacity-60">
                            Sin resultados para "{{ $search }}"
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>

    {{-- Área del mapa --}}
    <div class="relative min-h-0 flex-1">
        <div id="ubicatec-map" class="absolute inset-0"></div>

        {{-- Selector de piso + bottom-sheet --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-[900] flex flex-col items-center {{ $location ? '' : 'pb-4 sm:pb-6' }}">
            <div class="join pointer-events-auto mb-2 shadow-lg">
                <button type="button" data-floor-btn="0" onclick="UbicaTecMap.setFloor(0)" class="join-item btn btn-sm sm:btn-md">
                    Exterior
                </button>
                <button type="button" data-floor-btn="1" onclick="UbicaTecMap.setFloor(1)" class="join-item btn btn-sm sm:btn-md">
                    1º
                </button>
                <button type="button" data-floor-btn="2" onclick="UbicaTecMap.setFloor(2)" class="join-item btn btn-sm sm:btn-md">
                    2º
                </button>
            </div>

            @if ($location)
                <div x-data="{ open: true }" class="pointer-events-auto w-full">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-center gap-1 border-t border-base-300 bg-base-100 py-1.5 text-xs opacity-70"
                    >
                        <span x-show="open">Ocultar ▾</span>
                        <span x-show="!open" x-cloak>Ver información del lugar ▴</span>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="max-h-[42dvh] overflow-y-auto border-t border-base-300 bg-base-100 px-4 pb-5 pt-3 shadow-2xl"
                    >
                        <div class="flex items-start gap-3">
                            @if ($location->image)
                                <img
                                    src="{{ asset($location->image) }}"
                                    alt="{{ $location->name }}"
                                    class="h-16 w-16 shrink-0 rounded-lg object-cover"
                                >
                            @endif
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold leading-tight">{{ $location->name }}</h2>
                                @if ($location->description)
                                    <p class="mt-1 text-sm opacity-70">{{ $location->description }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($location->phone || $location->email || $location->website || $location->facebook)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($location->phone)
                                    <a href="tel:{{ $location->phone }}" class="btn btn-sm btn-outline">
                                        📞 {{ $location->phone }}
                                    </a>
                                @endif
                                @if ($location->email)
                                    <a href="mailto:{{ $location->email }}" class="btn btn-sm btn-outline">
                                        ✉️ Correo
                                    </a>
                                @endif
                                @if ($location->website)
                                    <a href="{{ $location->website }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                                        🌐 Sitio web
                                    </a>
                                @endif
                                @if ($location->facebook)
                                    <a href="{{ $location->facebook }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                                        📘 Facebook
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

<style>
    [x-cloak] {
        display: none !important;
    }
    .ubicatec-polygon-label {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        font-size: 9px;
        font-weight: 600;
        color: #1f2937;
        text-align: center;
        white-space: normal;
        pointer-events: none;
    }
    .ubicatec-polygon-label::before {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const payload = @json($mapPayload);

        const map = L.map('ubicatec-map', { zoomControl: true });

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 22,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        if (payload.location && payload.location.lat && payload.location.lng) {
            map.setView([payload.location.lat, payload.location.lng], 19);
        } else {
            map.setView(payload.campusCenter, 17);
        }

        function styleFor(feature) {
            const kind = feature?.properties?.kind;
            const fillColor = payload.palette[kind] ?? '#999999';

            return {
                color: '#000000',
                weight: 1,
                fillColor: fillColor,
                fillOpacity: 1,
            };
        }

        function onEachFeature(feature, layer) {
            const name = feature?.properties?.name;

            if (name) {
                layer.bindTooltip(name, {
                    permanent: true,
                    direction: 'center',
                    className: 'ubicatec-polygon-label',
                });
            }
        }

        const floorLayers = {};
        let currentFloor = payload.location ? (payload.location.floor ?? 0) : 0;

        function applyFloor(floor) {
            currentFloor = floor;

            [1, 2].forEach(function (f) {
                if (floorLayers[f] && map.hasLayer(floorLayers[f])) {
                    map.removeLayer(floorLayers[f]);
                }
            });

            if ((floor === 1 || floor === 2) && floorLayers[floor]) {
                floorLayers[floor].addTo(map);
            }

            document.querySelectorAll('[data-floor-btn]').forEach(function (btn) {
                btn.classList.toggle('btn-active', Number(btn.dataset.floorBtn) === floor);
            });
        }

        window.UbicaTecMap = { setFloor: applyFloor };

        Promise.all(
            [0, 1, 2].map(function (floor) {
                return fetch(payload.geo[floor])
                    .then(function (res) { return res.json(); })
                    .then(function (geojson) {
                        floorLayers[floor] = L.geoJSON(geojson, { style: styleFor, onEachFeature: onEachFeature });
                    });
            })
        ).then(function () {
            // El exterior (piso 0) siempre está de base.
            floorLayers[0].addTo(map);
            applyFloor(currentFloor);

            if (payload.location && payload.location.lat && payload.location.lng) {
                L.marker([payload.location.lat, payload.location.lng]).addTo(map);
            }
        });
    });
</script>
</div>
