<?php

namespace Tests\Feature;

use App\Livewire\Admin\LocationList;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LocationListBuildingFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_locations_by_building(): void
    {
        // search_count alto en las de Gimnasio para que "Salón 105" quede fuera
        // del top 3 de stats y assertDontSee solo dependa de la tabla filtrada.
        Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1, 'building' => 'Edificio Rivera Lara']);
        foreach (['Cancha techada', 'Duela', 'Gradas'] as $name) {
            Location::create(['name' => $name, 'slug' => str_replace(' ', '-', mb_strtolower($name)), 'floor' => 1, 'building' => 'Gimnasio', 'search_count' => 5]);
        }

        Livewire::test(LocationList::class)
            ->assertSee('Salón 105')
            ->assertSee('Cancha techada')
            ->set('building', 'Gimnasio')
            ->assertSee('Cancha techada')
            ->assertDontSee('Salón 105');
    }
}
