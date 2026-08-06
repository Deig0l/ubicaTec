<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusMapBuildingClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_floor0_urls_in_map_payload_and_info_page_renders(): void
    {
        Location::create(['name' => 'Gimnasio', 'slug' => 'gimnasio', 'floor' => 0, 'description' => 'Cancha techada del campus']);
        Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1, 'building' => 'Gimnasio']);

        // El payload del mapa trae la info del edificio (piso 0) y, como
        // directorio ("¿Qué hay en este edificio?"), sus espacios interiores.
        $this->get(route('map'))
            ->assertOk()
            ->assertSee('floor0Info')
            ->assertSee('gimnasio')
            ->assertSee('Cancha techada del campus')
            ->assertSee('salon-105');

        // La URL a la que navega el click muestra el panel de info.
        $this->get(route('map', 'gimnasio'))
            ->assertOk()
            ->assertSee('Cancha techada del campus');
    }
}
