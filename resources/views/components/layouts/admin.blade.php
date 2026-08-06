<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'UbicaTec Admin' }}</title>

    <link rel="icon" href="{{ asset('images/locations/logo_itcj.png') }}">

    {{-- Leaflet 1.9.4 (CDN, igual que el legacy) — para el mini-mapa del form --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Tema (anti-FOUC): fija data-theme antes de pintar --}}
    <script>
        (function () {
            const stored = localStorage.getItem('ubicatec-theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', stored || system);
            window.UbicaTecTheme = {
                toggle() {
                    const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('ubicatec-theme', next);
                }
            };
            if (!stored) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    if (!localStorage.getItem('ubicatec-theme')) document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                });
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-base-200 text-base-content antialiased">
    {{-- El toast va primero para que `window.toast` exista antes de cualquier
         disparo que ocurra durante el render inicial de la página. --}}
    <x-mary-toast position="toast-top toast-center" />

    <div class="navbar bg-base-100 shadow-sm px-4 sticky top-0 z-40">
        <div class="flex-1">
            <span class="text-lg font-semibold">UbicaTec Admin</span>
        </div>
        <div class="flex-none flex items-center gap-2">
            @auth
                <x-mary-button label="Estadísticas" icon="o-chart-bar" link="{{ route('admin.stats') }}" no-wire-navigate class="btn-ghost btn-sm" />
                <x-mary-button label="Categorías" icon="o-tag" link="{{ route('admin.categories') }}" no-wire-navigate class="btn-ghost btn-sm" />
            @endauth

            <x-mary-button label="Ver sitio" icon="o-globe-alt" link="/" no-wire-navigate class="btn-ghost btn-sm" />

            <button type="button" onclick="UbicaTecTheme.toggle()" aria-label="Cambiar tema" class="btn btn-circle btn-ghost btn-sm">
                <x-mary-icon name="o-moon" class="w-5 h-5 icon-theme-moon" />
                <x-mary-icon name="o-sun" class="w-5 h-5 icon-theme-sun" />
            </button>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-mary-button label="Salir" icon="o-arrow-right-on-rectangle" type="submit" class="btn-ghost btn-sm" />
                </form>
            @endauth
        </div>
    </div>

    <main class="p-4 lg:p-6 max-w-6xl mx-auto">
        {{ $slot }}
    </main>

</body>
</html>
