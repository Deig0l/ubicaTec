<?php

namespace Tests\Feature;

use App\Livewire\Welcome;
use App\Models\Location;
use App\Models\SearchTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchTermLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_miss_and_chosen_terms_are_logged(): void
    {
        $location = Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1, 'is_searchable' => true]);

        // Sin resultados → miss con location_id null.
        Livewire::test(Welcome::class)->set('search', 'cajero automatico');
        $this->assertDatabaseHas('search_terms', ['term' => 'cajero automatico', 'location_id' => null]);

        // Con resultado elegido → término ligado a la locación.
        Livewire::test(Welcome::class)->set('search', 'Salón 105')->call('go', $location->id);
        $this->assertDatabaseHas('search_terms', ['term' => 'salón 105', 'location_id' => $location->id]);

        // Términos cortos no se registran.
        Livewire::test(Welcome::class)->set('search', 'xy');
        $this->assertSame(0, SearchTerm::where('term', 'xy')->count());
    }

    public function test_spam_does_not_inflate_stats(): void
    {
        $salon = Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1]);
        $gym = Location::create(['name' => 'Gimnasio', 'slug' => 'gimnasio', 'floor' => 0]);

        // Misma IP + misma locación dentro de la ventana: solo cuenta la primera.
        $salon->registerSearchHit('salón 105');
        $salon->registerSearchHit('salón 105');
        $this->assertSame(1, $salon->fresh()->search_count);
        $this->assertSame(1, \App\Models\SearchHit::count());

        // Otra locación desde la misma IP sí cuenta (la llave incluye la locación).
        $gym->registerSearchHit();
        $this->assertSame(1, $gym->fresh()->search_count);

        // Mismo término fallido dos veces en la hora: una sola fila.
        SearchTerm::log('cajero', null);
        SearchTerm::log('cajero', null);
        $this->assertSame(1, SearchTerm::where('term', 'cajero')->count());

        // Tope global de misses por IP: 15 por hora ('cajero' ya consumió 1 → caben 14 más).
        foreach (range(1, 20) as $i) {
            SearchTerm::log("termino inventado {$i}", null);
        }
        $this->assertSame(14, SearchTerm::where('term', 'like', 'termino inventado%')->count());
    }

    public function test_stats_page_shows_terms_and_never_searched(): void
    {
        Location::create(['name' => 'Salón Fantasma', 'slug' => 'salon-fantasma', 'floor' => 1, 'is_searchable' => true]);
        SearchTerm::log('cajero automatico', null);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.stats'))
            ->assertOk()
            ->assertSee('cajero automatico')
            ->assertSee('Salón Fantasma')
            ->assertSee('Búsquedas sin resultado');
    }
}
