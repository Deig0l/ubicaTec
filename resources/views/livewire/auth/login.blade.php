<div class="flex items-center justify-center py-10 lg:py-16">
    <div class="card w-full max-w-sm bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex flex-col items-center mb-2 text-center">
                <img src="{{ asset('images/locations/logo_itcj.png') }}" alt="ITCJ" class="w-16 h-16 object-contain mb-2" />
                <h1 class="text-xl font-bold">Acceso administrador</h1>
                <p class="text-sm text-base-content/60">UbicaTec</p>
            </div>

            @if ($loginFailed)
                <div class="alert alert-error text-sm">
                    <x-mary-icon name="o-x-circle" class="w-5 h-5" />
                    <span>Credenciales inválidas.</span>
                </div>
            @endif

            <form wire:submit="authenticate" class="flex flex-col gap-3">
                <x-mary-input
                    label="Correo"
                    wire:model="email"
                    type="email"
                    icon="o-envelope"
                    autofocus
                    required
                />

                <x-mary-input
                    label="Contraseña"
                    wire:model="password"
                    type="password"
                    icon="o-lock-closed"
                    required
                />

                <x-mary-button label="Entrar" type="submit" class="btn-primary mt-2 w-full" spinner="authenticate" />
            </form>
        </div>
    </div>
</div>
