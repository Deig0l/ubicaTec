<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">{{ $location ? 'Editar locación' : 'Nueva locación' }}</h1>

        <x-mary-button
            label="Volver al listado"
            icon="o-arrow-left"
            link="{{ route('admin.locations') }}"
            no-wire-navigate
            class="btn-ghost btn-sm"
        />
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        {{-- Columna izquierda: datos generales --}}
        <div class="flex flex-col gap-4">
            <x-mary-input label="Nombre" wire:model="name" icon="o-building-office" required />

            <x-mary-textarea label="Descripción" wire:model="description" rows="3" />

            <div class="grid grid-cols-2 gap-3">
                <x-mary-select label="Piso" wire:model="floor" :options="$floorOptions" />
                <x-mary-select label="Tipo" wire:model="kind" :options="$kindOptions" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-mary-input label="Teléfono" wire:model="phone" icon="o-phone" />
                <x-mary-input label="Correo" wire:model="email" icon="o-envelope" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-mary-input label="Sitio web" wire:model="website" icon="o-globe-alt" />
                <x-mary-input label="Facebook" wire:model="facebook" icon="o-share" />
            </div>

            <x-mary-tags
                label="Sinónimos de búsqueda"
                wire:model="synonyms"
                hint="Presiona Enter o coma para agregar cada sinónimo"
                placeholder="Ej. gym, cafetería..."
            />

            <x-mary-toggle label="Aparece en el buscador público" wire:model="is_searchable" right />

            <div>
                <x-mary-file label="Fotografía" wire:model="photo" accept="image/*" hint="Máx. 4MB">
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-box" />
                    @elseif ($location?->image)
                        <img src="{{ asset($location->image) }}" class="h-24 w-24 object-cover rounded-box" />
                    @endif
                </x-mary-file>
            </div>
        </div>

        {{-- Columna derecha: ubicación en el mapa --}}
        <div class="flex flex-col gap-3">
            <span class="fieldset-legend">Ubicación en el mapa (haz clic o arrastra el marcador)</span>

            <div wire:ignore>
                <div id="admin-location-map" style="height: 300px;" class="rounded-box overflow-hidden border border-base-300 z-0"></div>

                <script>
                    (function () {
                        const map = L.map('admin-location-map').setView(
                            [{{ $lat ?? 31.719091 }}, {{ $lng ?? -106.422 }}],
                            17
                        );

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 20,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(map);

                        const marker = L.marker(
                            [{{ $lat ?? 31.719091 }}, {{ $lng ?? -106.422 }}],
                            { draggable: true }
                        ).addTo(map);

                        function placeMarker(latlng) {
                            marker.setLatLng(latlng);
                            @this.set('lat', latlng.lat);
                            @this.set('lng', latlng.lng);
                        }

                        map.on('click', (e) => placeMarker(e.latlng));
                        marker.on('dragend', () => placeMarker(marker.getLatLng()));
                    })();
                </script>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-mary-input label="Latitud" wire:model="lat" type="number" step="any" />
                <x-mary-input label="Longitud" wire:model="lng" type="number" step="any" />
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <x-mary-button label="Cancelar" link="{{ route('admin.locations') }}" no-wire-navigate class="btn-ghost" />
                <x-mary-button label="Guardar" type="submit" icon="o-check-circle" class="btn-primary" spinner="save" />
            </div>
        </div>
    </form>
</div>
