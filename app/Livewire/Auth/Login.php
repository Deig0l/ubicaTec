<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
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

        if (! Auth::attempt($credentials)) {
            $this->loginFailed = true;

            return;
        }

        request()->session()->regenerate();

        $this->redirect(route('admin.locations'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
