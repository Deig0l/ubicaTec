<div>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Estadísticas de búsqueda</h1>
            <p class="text-sm text-base-content/60">Qué se busca en el campus y cuándo.</p>
        </div>

        <div class="max-w-xs">
            <x-mary-select
                wire:model.live="range"
                :options="[
                    ['id' => '7', 'name' => 'Últimos 7 días'],
                    ['id' => '30', 'name' => 'Últimos 30 días'],
                    ['id' => '90', 'name' => 'Últimos 90 días'],
                    ['id' => 'all', 'name' => 'Todo'],
                ]"
                icon="o-calendar"
            />
        </div>
    </div>

    @if ($total === 0)
        <div class="alert alert-info mb-6">
            <x-mary-icon name="o-information-circle" class="h-5 w-5" />
            <span>
                Aún no hay búsquedas registradas con fecha y hora — se empezaron a registrar el 5 de agosto de 2026.
                El histórico anterior solo existe como total por locación (columna "Veces buscada").
            </span>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <x-mary-stat title="Búsquedas en el periodo" :value="$total" icon="o-magnifying-glass" color="text-primary" />
        <x-mary-stat title="Hora pico" :value="$peakHour" icon="o-clock" color="text-secondary" />
        <x-mary-stat title="Día pico" :value="$peakDow" icon="o-calendar-days" color="text-secondary" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="card bg-base-100 lg:row-span-2">
            <div class="card-body p-5">
                <h2 class="card-title text-base">Más buscadas en el periodo</h2>
                <table class="table table-sm">
                    <thead>
                        <tr><th></th><th>Locación</th><th>Edificio</th><th class="text-right">Búsquedas</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($top as $i => $row)
                            <tr>
                                <td class="text-base-content/40">{{ $i + 1 }}</td>
                                <td>{{ $row->name }}</td>
                                <td class="text-base-content/60">{{ $row->building ?? '—' }}</td>
                                <td class="text-right font-semibold">{{ $row->hits }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-base-content/40 py-4">Sin datos en este periodo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($charts as $chart)
            <div class="card bg-base-100">
                <div class="card-body p-5">
                    <h2 class="card-title text-base">{{ $chart['title'] }}</h2>

                    <div class="flex items-end gap-[2px] h-36 border-b border-base-300 pt-4">
                        @foreach ($chart['bars'] as $bar)
                            <div class="tooltip flex h-full flex-1 flex-col justify-end" data-tip="{{ $bar['tip'] }}">
                                @if ($chart['max'] > 0 && $bar['value'] === $chart['max'])
                                    <span class="mb-0.5 text-center text-[10px] font-semibold leading-none">{{ $bar['value'] }}</span>
                                @endif
                                <div
                                    class="w-full rounded-t hover:opacity-80"
                                    style="height: {{ $chart['max'] > 0 ? round($bar['value'] / $chart['max'] * 100) : 0 }}%; background: var(--ubicatec-chart-bar);"
                                ></div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex gap-[2px]">
                        @foreach ($chart['bars'] as $bar)
                            <span class="flex-1 text-center text-[10px] text-base-content/50">{{ $bar['label'] }}</span>
                        @endforeach
                    </div>

                    @if ($chart['note'])
                        <p class="text-xs text-base-content/40">{{ $chart['note'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-5">
                <h2 class="card-title text-base">Búsquedas sin resultado</h2>
                <p class="text-xs text-base-content/50 -mt-1">Lo que la gente buscó y no encontró — candidatos a sinónimos o locaciones nuevas.</p>
                <table class="table table-sm">
                    <thead><tr><th>Término</th><th class="text-right">Veces</th><th class="text-right">Última vez</th></tr></thead>
                    <tbody>
                        @forelse ($misses as $row)
                            <tr>
                                <td class="font-mono">{{ $row->term }}</td>
                                <td class="text-right font-semibold">{{ $row->veces }}</td>
                                <td class="text-right text-base-content/60">{{ \Illuminate\Support\Carbon::parse($row->ultima)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-base-content/40 py-4">Nada sin resultado en este periodo 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-5">
                <h2 class="card-title text-base">Términos más usados</h2>
                <p class="text-xs text-base-content/50 -mt-1">Cómo le llama la gente a los lugares al buscar (con resultado elegido).</p>
                <table class="table table-sm">
                    <thead><tr><th>Término</th><th class="text-right">Veces</th></tr></thead>
                    <tbody>
                        @forelse ($topTerms as $row)
                            <tr>
                                <td class="font-mono">{{ $row->term }}</td>
                                <td class="text-right font-semibold">{{ $row->veces }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-base-content/40 py-4">Sin datos en este periodo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-5">
                <h2 class="card-title text-base">Por categoría</h2>
                @php $catMax = $byCategory->max('hits') ?? 0; @endphp
                @forelse ($byCategory as $row)
                    <div class="flex items-center gap-3">
                        <span class="w-36 shrink-0 truncate text-sm">{{ $row->name }}</span>
                        <div class="h-3 flex-1">
                            <div class="h-full rounded-r" style="width: {{ $catMax > 0 ? max(round($row->hits / $catMax * 100), 2) : 0 }}%; background: var(--ubicatec-chart-bar);"></div>
                        </div>
                        <span class="w-10 shrink-0 text-right text-sm font-semibold">{{ $row->hits }}</span>
                    </div>
                @empty
                    <p class="text-center text-base-content/40 py-4 text-sm">Sin datos en este periodo</p>
                @endforelse
            </div>
        </div>

        <div class="card bg-base-100">
            <div class="card-body p-5">
                <h2 class="card-title text-base">Nunca buscadas ({{ $neverSearched->count() }})</h2>
                <p class="text-xs text-base-content/50 -mt-1">Buscables con 0 búsquedas históricas — ¿nombre poco conocido? ¿les falta sinónimo?</p>
                <div class="flex flex-wrap gap-1.5 max-h-48 overflow-y-auto">
                    @forelse ($neverSearched as $loc)
                        <span class="badge badge-ghost badge-sm">{{ $loc->name }}</span>
                    @empty
                        <p class="text-base-content/40 text-sm py-2">Todas las locaciones han sido buscadas al menos una vez 🎉</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
