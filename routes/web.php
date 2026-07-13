<?php

use App\Livewire\CampusMap;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('/mapa/{location:slug?}', CampusMap::class)->name('map');

require __DIR__.'/admin.php';
