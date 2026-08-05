<?php

use App\Livewire\CampusMap;
use App\Livewire\Discover;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('/mapa/{location:slug?}', CampusMap::class)->name('map');
Route::get('/descubrir/{category:slug?}', Discover::class)->name('discover');

require __DIR__.'/admin.php';
