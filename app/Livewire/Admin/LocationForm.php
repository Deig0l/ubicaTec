<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Locación · UbicaTec Admin')]
class LocationForm extends Component
{
    use Toast;
    use WithFileUploads;

    /**
     * Centro del campus ITCJ, usado como valor por defecto del mini-mapa.
     */
    private const DEFAULT_LAT = 31.719091;

    private const DEFAULT_LNG = -106.422;

    public ?Location $location = null;

    public string $name = '';

    public string $description = '';

    public int $floor = 0;

    public int $kind = 0;

    /** Nullable: puede quedar "Sin categoría" (cadena vacía desde el select). */
    public $category_id = null;

    public ?float $lat = self::DEFAULT_LAT;

    public ?float $lng = self::DEFAULT_LNG;

    public string $phone = '';

    public string $email = '';

    public string $website = '';

    public string $facebook = '';

    public bool $is_searchable = true;

    public $photo = null;

    public array $synonyms = [];

    public function mount(?Location $location = null): void
    {
        if ($location && $location->exists) {
            $this->location = $location;
            $this->name = $location->name;
            $this->description = (string) $location->description;
            $this->floor = $location->floor;
            $this->kind = $location->kind;
            $this->category_id = $location->category_id;
            $this->lat = $location->lat ?? self::DEFAULT_LAT;
            $this->lng = $location->lng ?? self::DEFAULT_LNG;
            $this->phone = (string) $location->phone;
            $this->email = (string) $location->email;
            $this->website = (string) $location->website;
            $this->facebook = (string) $location->facebook;
            $this->is_searchable = $location->is_searchable;
            $this->synonyms = $location->synonyms()->pluck('name')->all();
        }
    }

    /**
     * @return array<int, array{id:int, name:string}>
     */
    public function floorOptions(): array
    {
        return [
            ['id' => 0, 'name' => 'Exterior / Planta baja'],
            ['id' => 1, 'name' => '1er piso'],
            ['id' => 2, 'name' => '2do piso'],
        ];
    }

    /**
     * Catálogo `kind` (legacy Guia-GeoJSON.txt).
     *
     * @return array<int, array{id:int, name:string}>
     */
    public function kindOptions(): array
    {
        return [
            ['id' => 0, 'name' => 'Edificio'],
            ['id' => 2, 'name' => 'Escalera'],
            ['id' => 3, 'name' => 'Salón'],
            ['id' => 5, 'name' => 'Baños'],
            ['id' => 6, 'name' => 'Oficina'],
            ['id' => 7, 'name' => 'Estacionamiento'],
            ['id' => 10, 'name' => 'Elevador'],
            ['id' => 11, 'name' => 'Punto de venta'],
            ['id' => 12, 'name' => 'Laboratorio'],
            ['id' => 13, 'name' => 'Área de descanso'],
            ['id' => 14, 'name' => 'Bodega'],
            ['id' => 15, 'name' => 'Canchas'],
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('locations', 'name')->ignore($this->location?->id),
            ],
            'description' => ['nullable', 'string'],
            'floor' => ['required', 'integer', 'in:0,1,2'],
            'kind' => ['required', 'integer'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'is_searchable' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'synonyms' => ['array'],
            'synonyms.*' => ['string', 'max:255'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'phone' => 'teléfono',
            'website' => 'sitio web',
            'photo' => 'fotografía',
        ];
    }

    private function uniqueSlugFor(string $name): string
    {
        $base = Str::slug($name) ?: 'locacion';
        $slug = $base;
        $suffix = 2;

        while (
            Location::query()
                ->where('slug', $slug)
                ->when($this->location, fn ($q) => $q->whereKeyNot($this->location->id))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function syncSynonyms(): void
    {
        $names = collect($this->synonyms)
            ->map(fn ($s) => trim((string) $s))
            ->filter(fn ($s) => $s !== '')
            ->unique(fn ($s) => Str::lower($s))
            ->values();

        $this->location->synonyms()->delete();

        if ($names->isNotEmpty()) {
            $this->location->synonyms()->createMany(
                $names->map(fn ($name) => ['name' => $name])->all()
            );
        }
    }

    public function save(): void
    {
        // El select "Sin categoría" envía cadena vacía: la normalizamos a null.
        $this->category_id = ($this->category_id === '' || $this->category_id === null)
            ? null
            : (int) $this->category_id;

        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => $this->uniqueSlugFor($this->name),
            'description' => $this->description !== '' ? $this->description : null,
            'floor' => $this->floor,
            'kind' => $this->kind,
            'category_id' => $this->category_id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'phone' => $this->phone !== '' ? $this->phone : null,
            'email' => $this->email !== '' ? $this->email : null,
            'website' => $this->website !== '' ? $this->website : null,
            'facebook' => $this->facebook !== '' ? $this->facebook : null,
            'is_searchable' => $this->is_searchable,
        ];

        if ($this->photo) {
            $filename = Str::slug($this->name).'-'.now()->timestamp.'.'.$this->photo->extension();
            $this->photo->move(public_path('images/locations'), $filename);
            $data['image'] = 'images/locations/'.$filename;
        }

        if ($this->location) {
            $this->location->update($data);
        } else {
            $this->location = Location::create($data);
        }

        $this->syncSynonyms();

        // Se evita wire:navigate: se muestra el toast en la página actual y,
        // tras una breve pausa, se hace una navegación normal (no-SPA) al listado.
        $this->success('Locación guardada correctamente.');
        $this->js('setTimeout(() => { window.location.href = '.json_encode(route('admin.locations')).' }, 900)');
    }

    public function render()
    {
        return view('livewire.admin.location-form', [
            'floorOptions' => $this->floorOptions(),
            'kindOptions' => $this->kindOptions(),
            'categoryOptions' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
