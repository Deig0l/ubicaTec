<!DOCTYPE html>
<html lang="es-MX" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'UbicaTec' }} · ITCJ</title>

    <link rel="icon" href="{{ asset('images/locations/logo_itcj.png') }}">

    {{-- Precarga de recursos del mapa --}}
    <link rel="preconnect" href="https://tile.openstreetmap.org" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="preload" href="/geo/piso0.json" as="fetch" crossorigin>

    {{-- Leaflet 1.9.4 (CDN, igual que el legacy) --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-base-100 text-base-content antialiased" style="overscroll-behavior: none;">
    {{ $slot }}

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</body>
</html>
