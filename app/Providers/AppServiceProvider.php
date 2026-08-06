<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tope de volumen: toda interacción Livewire (búsquedas incluidas) pasa por
        // aquí; 120 req/min por IP corta scripts sin estorbar tecleo ni admin.
        // ponytail: si el campus entero sale por un NAT con una sola IP, subir el número.
        Livewire::setUpdateRoute(
            fn ($handle) => Route::post('/livewire/update', $handle)->middleware(['throttle:120,1', 'web'])
        );
    }
}
