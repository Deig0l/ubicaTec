<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\SearchHit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_search_hit_stores_timestamped_row(): void
    {
        $location = Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1]);

        $location->registerSearchHit();

        $this->assertSame(1, $location->fresh()->search_count);
        $this->assertSame(1, SearchHit::where('location_id', $location->id)->count());
    }

    public function test_stats_page_shows_top_and_requires_auth(): void
    {
        $location = Location::create(['name' => 'Salón 105', 'slug' => 'salon-105', 'floor' => 1, 'building' => 'Edificio Rivera Lara']);
        $location->registerSearchHit();
        $location->registerSearchHit();

        $this->get(route('admin.stats'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.stats'))
            ->assertOk()
            ->assertSee('Estadísticas de búsqueda')
            ->assertSee('Salón 105')
            ->assertSee('Edificio Rivera Lara');
    }
}
