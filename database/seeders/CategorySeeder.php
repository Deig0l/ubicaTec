<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categorías base del campus (name, slug, icon).
     *
     * @var array<int, array{name:string, slug:string, icon:string}>
     */
    private array $categories = [
        ['name' => 'Departamentos', 'slug' => 'departamentos', 'icon' => '🏛️'],
        ['name' => 'Administrativo', 'slug' => 'administrativo', 'icon' => '🏢'],
        ['name' => 'Salones', 'slug' => 'salones', 'icon' => '📖'],
        ['name' => 'Laboratorios', 'slug' => 'laboratorios', 'icon' => '🔬'],
        ['name' => 'Comida', 'slug' => 'comida', 'icon' => '🍔'],
        ['name' => 'Deportes', 'slug' => 'deportes', 'icon' => '⚽'],
        ['name' => 'Servicios', 'slug' => 'servicios', 'icon' => '🛎️'],
    ];

    /**
     * Seed idempotente: crea las 7 categorías y asigna category_id a las
     * locaciones existentes sin tocar ninguno de sus demás campos.
     */
    public function run(): void
    {
        // 1. Crear categorías (idempotente por slug).
        foreach ($this->categories as $data) {
            Category::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name'], 'icon' => $data['icon']],
            );
        }

        $id = fn (string $slug): int => Category::where('slug', $slug)->value('id');

        // 2. Reglas por `kind` (se aplican primero).
        Location::where('kind', 3)->update(['category_id' => $id('salones')]);
        Location::where('kind', 12)->update(['category_id' => $id('laboratorios')]);
        Location::where('kind', 6)->update(['category_id' => $id('administrativo')]);
        Location::where('kind', 11)->update(['category_id' => $id('comida')]);
        Location::where('kind', 15)->update(['category_id' => $id('deportes')]);

        // 3. Reglas por nombre (ILIKE, ignorando acentos) — sobrescriben kind.
        $this->assignByName(['Cafetería', 'Coffee Shop'], $id('comida'));

        $this->assignByName(['Gimnasio', 'Alberca'], $id('deportes'));
        Location::whereRaw('unaccent(name) ILIKE unaccent(?)', ['%cancha%'])
            ->update(['category_id' => $id('deportes')]);

        $this->assignByName([
            'Centro de Información (Biblioteca)',
            'Consultorio Médico',
            'Sala de Alumnos',
            'Liebre Shop',
        ], $id('servicios'));

        // 4. Departamentos (se aplican al final: ganan sobre la regla por kind).
        $this->assignByName([
            'Ciencias Básicas',
            'Económico Administrativo',
            'Eléctrica y Electrónica',
            'Industrial y Logística',
            'Metal Mecánica',
            'Sistemas Computacionales',
        ], $id('departamentos'));
    }

    /**
     * Asigna $categoryId a las locaciones cuyo nombre coincida exactamente
     * (ignorando acentos y mayúsculas) con alguno de $names.
     *
     * @param  array<int, string>  $names
     */
    private function assignByName(array $names, int $categoryId): void
    {
        foreach ($names as $name) {
            Location::whereRaw('unaccent(name) ILIKE unaccent(?)', [$name])
                ->update(['category_id' => $categoryId]);
        }
    }
}
