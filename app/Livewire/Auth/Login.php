<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Iniciar sesión · UbicaTec Admin')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $loginFailed = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('admin.locations'));
        }
    }

    public function authenticate(): void
    {
        $this->loginFailed = false;

        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Anti fuerza bruta: 5 intentos por email+IP, bloqueo de 1 minuto.
        $throttleKey = mb_strtolower($this->email).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($throttleKey).' segundos.',
            ]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, 60);
            $this->loginFailed = true;

            return;
        }

        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();

        $this->redirect(route('admin.locations'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
