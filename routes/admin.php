<?php

// Rutas de auth y panel admin — propiedad del carril C (ver docs/superpowers/specs).

use App\Livewire\Admin\CategoryList;
use App\Livewire\Admin\LocationForm;
use App\Livewire\Admin\LocationList;
use App\Livewire\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)
    ->middleware('guest')
    ->name('login');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/', LocationList::class)->name('admin.locations');
    Route::get('/locaciones/nueva', LocationForm::class)->name('admin.locations.create');
    Route::get('/locaciones/{location}/editar', LocationForm::class)->name('admin.locations.edit');
    Route::get('/categorias', CategoryList::class)->name('admin.categories');
});
