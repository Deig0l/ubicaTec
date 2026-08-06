<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use App\Models\SearchHit;
use App\Models\SearchTerm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Estadísticas · UbicaTec Admin')]
class Stats extends Component
{
    /** Días hacia atrás: '7' | '30' | '90' | 'all'. */
    public string $range = '30';

    private const DOW_LABELS = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 7 => 'Dom'];

    private function inRange()
    {
        return SearchHit::query()->when(
            $this->range !== 'all',
            fn ($q) => $q->where('search_hits.created_at', '>=', now()->subDays((int) $this->range))
        );
    }

    private function termsInRange()
    {
        return SearchTerm::query()->when(
            $this->range !== 'all',
            fn ($q) => $q->where('created_at', '>=', now()->subDays((int) $this->range))
        );
    }

    /** @return array{title: string, note: string, bars: list<array{label: string, value: int, tip: string}>, max: int} */
    private function chart(string $title, string $note, array $bars): array
    {
        return ['title' => $title, 'note' => $note, 'bars' => $bars, 'max' => max(array_column($bars, 'value') ?: [0])];
    }

    public function render()
    {
        $byHour = $this->inRange()
            ->selectRaw('EXTRACT(HOUR FROM created_at)::int AS k, COUNT(*) AS c')
            ->groupBy('k')->pluck('c', 'k');
        $hours = $this->chart('Por hora del día', 'Hora local de Juárez', collect(range(0, 23))->map(fn ($h) => [
            'label' => $h % 3 === 0 ? (string) $h : '',
            'value' => (int) ($byHour[$h] ?? 0),
            'tip' => sprintf('%02d:00 · %d', $h, $byHour[$h] ?? 0),
        ])->all());

        $byDow = $this->inRange()
            ->selectRaw('EXTRACT(ISODOW FROM created_at)::int AS k, COUNT(*) AS c')
            ->groupBy('k')->pluck('c', 'k');
        $dows = $this->chart('Por día de la semana', '', collect(range(1, 7))->map(fn ($d) => [
            'label' => self::DOW_LABELS[$d],
            'value' => (int) ($byDow[$d] ?? 0),
            'tip' => self::DOW_LABELS[$d].' · '.($byDow[$d] ?? 0),
        ])->all());

        $byMonth = SearchHit::where('created_at', '>=', now()->startOfMonth()->subMonths(11))
            ->selectRaw("to_char(created_at, 'YYYY-MM') AS k, COUNT(*) AS c")
            ->groupBy('k')->pluck('c', 'k');
        $months = $this->chart('Por mes', 'Últimos 12 meses (ignora el filtro de periodo)', collect(range(11, 0))->map(function ($back) use ($byMonth) {
            $m = now()->startOfMonth()->subMonths($back);
            $n = (int) ($byMonth[$m->format('Y-m')] ?? 0);
            $label = ucfirst($m->locale('es')->isoFormat('MMM'));

            return ['label' => $label, 'value' => $n, 'tip' => ucfirst($m->locale('es')->isoFormat('MMMM YYYY')).' · '.$n];
        })->all());

        $peakHour = collect($hours['bars'])->sortByDesc('value')->first();
        $peakDow = collect($dows['bars'])->sortByDesc('value')->first();

        return view('livewire.admin.stats', [
            'total' => $this->inRange()->count(),
            'top' => $this->inRange()
                ->join('locations', 'locations.id', '=', 'search_hits.location_id')
                ->selectRaw('locations.name, locations.building, locations.floor, COUNT(*) AS hits')
                ->groupBy('locations.name', 'locations.building', 'locations.floor')
                ->orderByDesc('hits')->limit(10)->get(),
            'peakHour' => $peakHour['value'] > 0 ? explode(' ', $peakHour['tip'])[0] : '—',
            'peakDow' => $peakDow['value'] > 0 ? $peakDow['label'] : '—',
            'charts' => [$hours, $dows, $months],
            'misses' => $this->termsInRange()->whereNull('location_id')
                ->selectRaw('term, COUNT(*) AS veces, MAX(created_at) AS ultima')
                ->groupBy('term')->orderByDesc('veces')->limit(15)->get(),
            'topTerms' => $this->termsInRange()->whereNotNull('location_id')
                ->selectRaw('term, COUNT(*) AS veces')
                ->groupBy('term')->orderByDesc('veces')->limit(15)->get(),
            'byCategory' => $this->inRange()
                ->join('locations', 'locations.id', '=', 'search_hits.location_id')
                ->leftJoin('categories', 'categories.id', '=', 'locations.category_id')
                ->selectRaw("COALESCE(categories.name, 'Sin categoría') AS name, COUNT(*) AS hits")
                ->groupBy('categories.name')->orderByDesc('hits')->get(),
            'neverSearched' => Location::where('search_count', 0)->where('is_searchable', true)
                ->orderBy('name')->get(['name']),
        ]);
    }
}
