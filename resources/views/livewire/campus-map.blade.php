<div class="flex flex-col bg-base-200" style="height:100vh; height:100dvh;" x-data="{ searchOpen: false, legendOpen: false, goingTo: null }">
    {{-- Barra superior fija --}}
    <div class="z-[1000] flex shrink-0 items-center justify-between gap-2 border-b border-base-300 bg-base-100 px-2 py-2 shadow-sm">
        <a
            href="{{ route('home') }}"
            aria-label="Volver al inicio"
            class="btn btn-circle btn-ghost btn-sm sm:btn-md shrink-0"
        >
            <x-mary-icon name="o-arrow-left" class="w-5 h-5" />
        </a>

        <div class="relative hidden min-w-0 flex-1 lg:block" wire:key="mapa-buscador">
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
                            @click="goingTo = @js($suggestion->name)"
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

        <button
            type="button"
            onclick="UbicaTecTheme.toggle()"
            aria-label="Cambiar tema"
            class="btn btn-circle btn-ghost btn-sm sm:btn-md shrink-0"
        >
            <x-mary-icon name="o-moon" class="w-5 h-5 icon-theme-moon" />
            <x-mary-icon name="o-sun" class="w-5 h-5 icon-theme-sun" />
        </button>
    </div>

    {{-- Área del mapa --}}
    <div class="relative min-h-0 flex-1">
        <div id="ubicatec-map" class="absolute inset-0"></div>

        {{-- Overlay de carga --}}
        <div id="ubicatec-map-loading" class="pointer-events-none absolute inset-0 z-[950] flex items-center justify-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        {{-- Botón "Mi ubicación" --}}
        <button
            type="button"
            onclick="UbicaTecMap.locateMe()"
            aria-label="Mostrar mi ubicación"
            class="pointer-events-auto absolute bottom-40 right-4 z-[950] btn btn-circle btn-primary shadow-lg lg:bottom-24"
        >
            <x-mary-icon name="o-map-pin" class="w-6 h-6" />
        </button>

        {{-- Leyenda de colores (colapsable) --}}
        <button
            type="button"
            @click="legendOpen = !legendOpen"
            aria-label="Mostrar leyenda de colores"
            class="pointer-events-auto absolute bottom-40 left-4 z-[950] btn btn-circle bg-base-100 text-base-content shadow-lg lg:bottom-24"
        >
            <x-mary-icon name="o-list-bullet" class="h-6 w-6" />
        </button>

        <div
            x-show="legendOpen"
            x-cloak
            class="pointer-events-auto absolute bottom-56 left-4 z-[950] max-h-64 w-48 overflow-y-auto rounded-box border border-base-300 bg-base-100 p-3 text-xs text-base-content shadow-xl lg:bottom-40"
        >
            <p class="mb-2 font-semibold">Leyenda</p>
            <div class="grid gap-1.5">
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#800026"></span><span>Edificio</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#f0e1cb"></span><span>Salón</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#FD8D3C"></span><span>Laboratorio</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#c0392b"></span><span>Oficina</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#bb8fce"></span><span>Punto de venta</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#FEB24C"></span><span>Área de descanso</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#52be80"></span><span>Canchas</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#b9b9b9"></span><span>Estacionamiento</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#0080ff"></span><span>Baños hombres</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#ff00ec"></span><span>Baños mujeres</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#2cff1b"></span><span>Escaleras</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-sm" style="background:#E31A1C"></span><span>Elevador</span></div>
            </div>
        </div>

        {{-- Selector de piso + bottom-sheet --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 z-[900] flex flex-col items-center lg:contents {{ $location ? '' : 'pb-4 sm:pb-6' }}">
            {{-- Píldora de búsqueda en zona del pulgar (solo móvil) --}}
            <button
                type="button"
                @click="searchOpen = true"
                class="pointer-events-auto z-[900] mx-4 mb-3 flex h-12 items-center gap-3 self-stretch rounded-full border border-base-300 bg-base-100 px-5 shadow-lg lg:hidden"
            >
                <x-mary-icon name="o-magnifying-glass" class="h-5 w-5 shrink-0 opacity-60" />
                <span class="text-sm opacity-60">¿A dónde vamos a ir?</span>
            </button>

            <div class="join pointer-events-auto z-[900] mb-2 shadow-lg lg:absolute lg:bottom-6 lg:left-1/2 lg:mb-0 lg:-translate-x-1/2">
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
                <div
                    x-data="{ open: true }"
                    :class="{ 'lg:flex': open, 'lg:hidden': !open }"
                    class="pointer-events-auto z-[900] w-full lg:absolute lg:left-4 lg:top-4 lg:max-h-[70vh] lg:w-96 lg:flex-col lg:overflow-hidden lg:rounded-box lg:border lg:border-base-300 lg:bg-base-100 lg:shadow-2xl"
                >
                    {{-- Cerrar (solo desktop, patrón tarjeta lateral) --}}
                    <button
                        type="button"
                        @click="open = false"
                        aria-label="Cerrar información"
                        class="btn btn-circle btn-ghost btn-xs absolute right-2 top-2 z-10 hidden lg:flex"
                    >
                        <x-mary-icon name="o-x-mark" class="h-4 w-4" />
                    </button>

                    {{-- Barra para colapsar (solo móvil) --}}
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-center gap-1 border-t border-base-300 bg-base-100 py-1.5 text-xs opacity-70 lg:hidden"
                    >
                        <span x-show="open">Ocultar ▾</span>
                        <span x-show="!open" x-cloak>Ver información del lugar ▴</span>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        class="max-h-[42dvh] overflow-y-auto border-t border-base-300 bg-base-100 px-4 pb-5 pt-3 shadow-2xl lg:max-h-none lg:flex-1 lg:border-t-0 lg:pr-8 lg:pt-9 lg:shadow-none"
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

    {{-- Sheet de búsqueda a pantalla completa (solo móvil) --}}
    <div
        x-show="searchOpen"
        x-cloak
        x-effect="searchOpen && setTimeout(() => $refs.searchInput && $refs.searchInput.querySelector('input')?.focus(), 80)"
        class="fixed inset-0 z-[1100] flex flex-col bg-base-100"
    >
        <div class="flex items-center gap-2 border-b border-base-300 px-3 py-3">
            <button
                type="button"
                @click="searchOpen = false"
                aria-label="Cerrar búsqueda"
                class="btn btn-circle btn-ghost btn-sm shrink-0"
            >
                <x-mary-icon name="o-x-mark" class="h-5 w-5" />
            </button>
            <div class="min-w-0 flex-1" x-ref="searchInput" wire:key="sheet-buscador">
                <x-mary-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="¿A dónde vamos a ir?"
                    icon="o-magnifying-glass"
                    autocomplete="off"
                />
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto" wire:key="sheet-sugerencias">
            @if (trim($search) !== '')
                @forelse ($this->suggestions as $suggestion)
                    <button
                        type="button"
                        wire:click="chooseLocation({{ $suggestion->id }})"
                        @click="goingTo = @js($suggestion->name)"
                        wire:key="sheet-sug-{{ $suggestion->id }}"
                        class="flex w-full items-center justify-between gap-3 border-b border-base-200 px-4 py-3.5 text-left last:border-0 hover:bg-base-200 active:bg-base-300"
                    >
                        <span class="font-medium">{{ $suggestion->name }}</span>
                        <span class="badge badge-ghost badge-sm shrink-0">
                            {{ $suggestion->floor === 0 ? 'Exterior' : 'Piso '.$suggestion->floor }}
                        </span>
                    </button>
                @empty
                    <div class="px-4 py-4 text-sm opacity-60">
                        Sin resultados para "{{ $search }}"
                    </div>
                @endforelse
            @else
                <p class="px-4 py-6 text-center text-sm opacity-60">
                    Escribe el nombre de un lugar del campus.
                </p>
            @endif
        </div>
    </div>

    {{-- Overlay "llevándote a…" durante la navegación --}}
    <div
        x-show="goingTo"
        x-cloak
        class="fixed inset-0 z-[1200] flex flex-col items-center justify-center gap-4 bg-base-100/80 backdrop-blur"
    >
        <span class="loading loading-spinner loading-lg text-primary"></span>
        <p class="text-sm font-medium">Llevándote a <span x-text="goingTo"></span>…</p>
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
        font-weight: 600;
        color: #1f2937;
        text-align: center;
        white-space: normal;
        pointer-events: none;
    }
    .ubicatec-polygon-label::before {
        display: none !important;
    }
    /* Dos niveles de etiqueta: edificios (piso 0) y salones (pisos 1-2).
       La visibilidad/tamaño depende del bucket de zoom puesto en #ubicatec-map. */
    .ubicatec-label-building { font-size: 11px; }
    .ubicatec-label-room { font-size: 9px; }
    .ubicatec-zoom-far .ubicatec-polygon-label { display: none !important; }
    .ubicatec-zoom-mid .ubicatec-label-room { display: none !important; }
    /* Viendo un piso interior, los nombres de edificio (exterior) estorban. */
    .ubicatec-floor-interior .ubicatec-label-building { display: none !important; }
    .ubicatec-zoom-close .ubicatec-label-room { font-size: 10px; }
    .ubicatec-zoom-closest .ubicatec-label-room { font-size: 13px; }
    .ubicatec-zoom-closest .ubicatec-label-building { font-size: 15px; }
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

        // Etiquetas por zoom: lejos = nada, medio = solo edificios,
        // cerca = aparecen salones, muy cerca = salones grandes.
        const mapEl = document.getElementById('ubicatec-map');
        const ZOOM_BUCKETS = ['ubicatec-zoom-far', 'ubicatec-zoom-mid', 'ubicatec-zoom-close', 'ubicatec-zoom-closest'];
        function syncLabels() {
            const z = map.getZoom();
            const bucket = z < 16 ? 'ubicatec-zoom-far'
                : z < 20 ? 'ubicatec-zoom-mid'
                : z < 22 ? 'ubicatec-zoom-close'
                : 'ubicatec-zoom-closest';
            ZOOM_BUCKETS.forEach(function (c) { mapEl.classList.toggle(c, c === bucket); });
        }
        map.on('zoomend', syncLabels);
        syncLabels();

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

        function onEachFeature(floor) {
            return function (feature, layer) {
                const name = feature?.properties?.name;

                if (name) {
                    const tier = floor === 0 ? 'ubicatec-label-building' : 'ubicatec-label-room';
                    layer.bindTooltip(name, {
                        permanent: true,
                        direction: 'center',
                        className: 'ubicatec-polygon-label ' + tier,
                    });
                }
            };
        }

        const floorLayers = {};
        let currentFloor = payload.location ? (payload.location.floor ?? 0) : 0;
        mapEl.classList.toggle('ubicatec-floor-interior', currentFloor === 1 || currentFloor === 2);

        function applyFloor(floor) {
            currentFloor = floor;
            mapEl.classList.toggle('ubicatec-floor-interior', floor === 1 || floor === 2);

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

        function hideMapLoading() {
            const el = document.getElementById('ubicatec-map-loading');
            if (el) el.remove();
        }

        // --- Geolocalización del usuario ---
        let userLocationMarker = null;
        let userAccuracyCircle = null;

        function showGeoToast(message) {
            let toast = document.getElementById('ubicatec-geo-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'ubicatec-geo-toast';
                toast.className = 'toast toast-center toast-bottom z-[1100]';
                document.body.appendChild(toast);
            }
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning shadow-lg';
            alert.textContent = message;
            toast.appendChild(alert);
            setTimeout(function () { alert.remove(); }, 4000);
        }

        // Los listeners se registran una sola vez (dentro de este init único).
        map.on('locationfound', function (e) {
            if (userLocationMarker) {
                userLocationMarker.setLatLng(e.latlng);
            } else {
                userLocationMarker = L.circleMarker(e.latlng, {
                    radius: 8,
                    color: '#ffffff',
                    weight: 2,
                    fillColor: '#2563eb',
                    fillOpacity: 0.9,
                }).addTo(map);
            }

            if (userAccuracyCircle) {
                userAccuracyCircle.setLatLng(e.latlng).setRadius(e.accuracy);
            } else {
                userAccuracyCircle = L.circle(e.latlng, {
                    radius: e.accuracy,
                    color: '#2563eb',
                    weight: 1,
                    fillColor: '#2563eb',
                    fillOpacity: 0.15,
                }).addTo(map);
            }

            map.setView(e.latlng, 18);
        });

        map.on('locationerror', function () {
            showGeoToast('No pudimos obtener tu ubicación. Revisa los permisos.');
        });

        function locateMe() {
            map.locate({ enableHighAccuracy: true });
        }

        window.UbicaTecMap = { setFloor: applyFloor, locateMe: locateMe };

        function paintFloor(floor, geojson) {
            floorLayers[floor] = L.geoJSON(geojson, { style: styleFor, onEachFeature: onEachFeature(floor) });

            if (floor === 0) {
                // El exterior (piso 0) siempre está de base; se pinta apenas llega su json.
                floorLayers[0].addTo(map);
                hideMapLoading();
            } else if (floor === currentFloor) {
                // Un piso interior sólo se muestra si es el actualmente seleccionado.
                floorLayers[floor].addTo(map);
            }

            // Mantener sincronizado el estado activo de los botones.
            document.querySelectorAll('[data-floor-btn]').forEach(function (btn) {
                btn.classList.toggle('btn-active', Number(btn.dataset.floorBtn) === currentFloor);
            });
        }

        // Los 3 fetch corren en paralelo; cada capa se pinta apenas llega SU json.
        Promise.all(
            [0, 1, 2].map(function (floor) {
                return fetch(payload.geo[floor])
                    .then(function (res) { return res.json(); })
                    .then(function (geojson) { paintFloor(floor, geojson); });
            })
        ).then(function () {
            if (payload.location && payload.location.lat && payload.location.lng) {
                L.marker([payload.location.lat, payload.location.lng]).addTo(map);
            }
        });
    });
</script>
</div>
