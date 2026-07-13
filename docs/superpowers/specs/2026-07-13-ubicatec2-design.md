# UbicaTec 2.0 — Spec y contrato de implementación

Fecha: 2026-07-13. Aprobado por el usuario.

## Visión

App mobile-first para ubicar lugares dentro del ITCJ (Tec de Cd. Juárez). Al abrir:
"¿A dónde vamos a ir?" con buscador y el top 3 de sitios más buscados. Elegir un
lugar lleva al mapa (Leaflet) con el pin y la info del lugar. Un panel admin (con
login) administra locaciones, sinónimos de búsqueda y el contador de búsquedas.

## Stack (fijo)

- Laravel 12, Livewire 3 (componentes de clase, NO Volt), MaryUI (daisyUI + Tailwind 4)
- PostgreSQL 18 local: `DB_HOST=/var/run/postgresql`, `DB_DATABASE=ubicatec`, usuario peer `deigol`, sin password. Extensión `unaccent` ya creada.
- Leaflet 1.9.4 por CDN (unpkg, igual que el legacy). NO instalar leaflet por npm.
- Assets legacy en `legacy/` (piso0/1/2.js con GeoJSON, Guia-GeoJSON.txt, imagenes/).

## Contrato de datos (NO cambiar nombres de columnas)

```
locations
  id            bigserial pk
  name          string  unique
  slug          string  unique
  description   text    nullable
  floor         smallint default 0      // 0=exterior/PB, 1, 2
  kind          smallint default 0      // catálogo legacy 0..15 (ver legacy/Guia-GeoJSON.txt)
  lat           double  nullable
  lng           double  nullable
  image         string  nullable        // ej. images/locations/gym.jpg (relativo a public/)
  phone         string  nullable
  email         string  nullable
  website       string  nullable
  facebook      string  nullable
  search_count  integer default 0
  is_searchable boolean default true
  timestamps

location_synonyms
  id           bigserial pk
  location_id  fk -> locations, cascadeOnDelete
  name         string
  unique(location_id, name)

users (default Laravel) — seed: admin@ubicatec.test / password "ubicatec2026"
```

Modelo `App\Models\Location`:
- `synonyms()` hasMany.
- `scopeSearch($q, string $term)`: `is_searchable=true` y (unaccent(name) ILIKE unaccent(%term%) O algún sinónimo igual con unaccent/ILIKE). Orden: primero prefijo exacto, luego `search_count` desc. Límite lo pone el caller. Usar `whereRaw` con bindings, jamás interpolar el término.
- `registerSearchHit()`: `increment('search_count')`.
- Slug generado de name en el seeder/form (Str::slug), único.

## Rutas (contrato)

- `GET /` → `App\Livewire\Welcome` — name `home`
- `GET /mapa/{location:slug?}` → `App\Livewire\CampusMap` — name `map`
- `routes/web.php` es del carril B e incluye al final: `require __DIR__.'/admin.php';`
- `routes/admin.php` es del carril C: `GET /login` (name `login`, componente `App\Livewire\Auth\Login`), `POST /logout` (name `logout`), grupo `middleware('auth')` con prefijo `/admin`: `GET /admin` → `Admin\LocationList` (name `admin.locations`), `GET /admin/locaciones/nueva` y `GET /admin/locaciones/{location}/editar` → `Admin\LocationForm` (names `admin.locations.create` / `admin.locations.edit`).

## Propiedad de archivos por carril (evitar colisiones)

- **Carril A (datos)**: `database/migrations/*`, `app/Models/*`, `database/seeders/*`,
  `scripts/convert-geojson.mjs`, `public/geo/piso{0,1,2}.json`, `public/images/locations/*`.
  Único carril que corre `php artisan migrate:fresh --seed`.
- **Carril B (público)**: `routes/web.php`, `resources/views/components/layouts/app.blade.php`,
  `app/Livewire/Welcome.php` + blade, `app/Livewire/CampusMap.php` + blade, JS de mapa inline en sus blades.
- **Carril C (admin)**: `routes/admin.php`, `resources/views/components/layouts/admin.blade.php`,
  `app/Livewire/Auth/Login.php`, `app/Livewire/Admin/LocationList.php`, `app/Livewire/Admin/LocationForm.php` + blades.
- Nadie toca archivos de otro carril. `resources/css/app.css` ya está configurado (daisyUI + mary) — no tocarlo.

## Carril A — detalles

1. `scripts/convert-geojson.mjs` (node): lee `legacy/pisoN.js` (son JS con comentarios,
   NO JSON), los evalúa con `new Function(src + '; return pisoNData;')` y escribe
   `public/geo/pisoN.json`. Correrlo una vez.
2. Copiar `legacy/imagenes/*.{jpg,jpeg,png}` a `public/images/locations/`.
3. Seeder:
   a. 14 lugares principales (del legacy, con foto e sinónimos iniciales):
      Ciencias Básicas (ciencias-basicas.jpg), Económico Administrativo (eco-admin.jpg),
      Eléctrica y Electrónica (electro.jpg), Industrial y Logística (industrial.jpg),
      Metal Mecánica (metalMecanica.jpg), Sistemas Computacionales (sistemas.jpg; sinónimos: ISC, sistemas, cómputo),
      Alberca (Alberca.jpg; sinónimo: piscina), Cafetería (Cafeteria.jpg; sinónimo: comedor),
      Centro de Información (Biblioteca) (Biblioteca.jpg; sinónimos: biblioteca, CI),
      Coffee Shop (Coffee-Shop.jpg; sinónimo: café), Consultorio Médico (consultorio-medico.jpg; sinónimos: doctor, enfermería),
      Gimnasio (gym.jpg; sinónimo: gym), Liebre Shop (liebre-shop.jpg), Sala de Alumnos (sala-alumnos.jpg).
      Descripciones: 1-2 frases genéricas razonables. Contactos vacíos (se llenan en admin).
      lat/lng: si un feature de piso0.json coincide por nombre (sin acentos, parcial) usar el
      centroide de su polígono (¡GeoJSON es [lng, lat]!); si no, usar el centro del campus
      31.719091, -106.422 como placeholder.
   b. Espacios interiores desde piso0/1/2.json: crear location por feature con kind en
      {0 edificio, 3 salón, 6 oficina, 11 punto venta, 12 laboratorio, 13 área descanso, 15 canchas}
      y nombre no genérico. Saltar: Baños, Escaleras, Acceso, Pasillo, Bodega, Cubículo, Puerta,
      y cualquier nombre ya existente como location o sinónimo (unaccent, case-insensitive).
      floor = número del archivo. lat/lng = centroide del primer ring del polígono.
      Poner search_count=0 en todo; a Biblioteca, Cafetería y Gimnasio darles search_count 5,4,3
      para que el top 3 inicial no esté vacío.
   c. Usuario admin (arriba).
4. Verificar: `php artisan migrate:fresh --seed` sin errores y `Location::count()` razonable (>40).

## Carril B — detalles

- Layout `app.blade.php`: mobile-first, `<meta viewport>`, fondo claro, incluye @vite,
  CDN de Leaflet (css+js) y `@livewireStyles/@livewireScripts` según convención Livewire 3
  (en L3 basta @vite; usar `<x-mary-*>` requiere el layout estándar). Sin wire:navigate.
- `Welcome`: pantalla completa centrada: logo (`/images/locations/logo_itcj.png`),
  título grande "¿A dónde vamos a ir?", input de búsqueda grande (wire:model.live.debounce.300ms),
  lista de sugerencias (máx 8, `Location::search()`), y sección "Los más buscados" con el
  top 3 (`orderByDesc(search_count)->limit(3)`, tarjetas con foto si hay).
  Acción `go($locationId)`: incrementa search_count y `redirect()->route('map', slug)`.
- `CampusMap`: mapa Leaflet full-screen (100dvh menos barra superior). Carga
  `/geo/piso0.json`, `/geo/piso1.json`, `/geo/piso2.json` con fetch. Estilo de polígonos por
  `feature.properties.kind` con la misma paleta del legacy (ver legacy). Selector de piso:
  3 botones flotantes abajo-centro (Exterior / 1º / 2º). Si hay location seleccionada:
  centrar [lat,lng] zoom 19, marcador, piso inicial = location.floor, y bottom-sheet
  (tarjeta fija abajo, colapsable) con nombre, descripción, teléfono/email/web/facebook
  (solo los no vacíos, con íconos) y foto. Barra superior: botón atrás (a `/`) + input de
  búsqueda que al elegir sugerencia hace redirect a `/mapa/{slug}` (recarga completa, sin SPA).
  Tooltips con nombre en cada polígono (como el legacy).
- Todo el JS del mapa inline en el blade de CampusMap con `@json(...)` para pasar datos.
- Diseño: MaryUI/daisyUI, limpio, botones táctiles grandes, español.

## Carril C — detalles

- Layout `admin.blade.php`: mary layout con navbar simple (título "UbicaTec Admin",
  botón logout) y CDN Leaflet incluido (para el mini-mapa del form).
- `Auth\Login`: form email+password (mary), `Auth::attempt`, redirect a `admin.locations`.
  Layout app o propio minimal. Error genérico si falla.
- `Admin\LocationList`: `<x-mary-table>` con búsqueda por texto, columnas: nombre, piso,
  sinónimos (badges), veces buscada (sortable), buscable (toggle), acciones (editar,
  eliminar con confirm, resetear contador). Paginación 15. Botón "Nueva locación".
- `Admin\LocationForm` (crear/editar): mary inputs para name, description (textarea),
  floor (select 0/1/2), kind (select con el catálogo del Guia), phone, email, website,
  facebook, is_searchable (toggle), foto (file upload opcional → `public/images/locations/`,
  usar WithFileUploads y storeAs en disco public con symlink `storage:link`... más simple:
  mover a `public/images/locations` con `$file->storeAs`? Usar disco público estándar y
  guardar path accesible; lo importante: la vista pública muestra `asset($location->image)`
  — mantener compatibilidad: guardar en `public/images/locations/` con move()).
  Sinónimos: `<x-mary-tags>` wire:model (array) → sync a location_synonyms al guardar.
  Pin: mini-mapa Leaflet (~300px) centrado en el campus; click coloca marker y setea
  `lat`/`lng` vía `@this.set`; inputs numéricos lat/lng visibles como respaldo.
  Validación: name required+unique (ignorando el propio id), slug auto de name.
- Al guardar → volver a la lista con toast mary.

## Verificación final

- `php artisan migrate:fresh --seed` OK, `npm run build` OK.
- Smoke móvil (390×844): `/` muestra pregunta+top3; buscar "gym" sugiere Gimnasio;
  elegir → mapa con pin y bottom-sheet; cambiar de piso funciona.
- Login admin → CRUD: crear locación con sinónimo, buscarla en `/`, encontrarla.

## Fuera de alcance (v1)

Dibujo/edición de polígonos, rutas/navegación paso a paso, PWA/offline, multi-idioma,
roles múltiples, "Ubica mi horario" y "Directorio" del legacy.
