<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

#[Layout('components.layouts.admin')]
#[Title('Categorías · UbicaTec Admin')]
class CategoryList extends Component
{
    use Toast;

    /** Campos del formulario de alta. */
    public string $name = '';

    public string $icon = '';

    /** Estado de la edición (modal). */
    public ?int $editingId = null;

    public string $editName = '';

    public string $editIcon = '';

    public bool $showEditModal = false;

    /**
     * Determina si ya existe una categoría con ese nombre (case-insensitive),
     * ignorando opcionalmente el propio id (al renombrar).
     */
    private function nameExists(string $name, ?int $ignoreId): bool
    {
        return Category::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }

    /**
     * Genera un slug único a partir del nombre (sufijo -2, -3… si choca).
     */
    private function uniqueSlug(string $name, ?int $ignoreId): string
    {
        $base = Str::slug($name) ?: 'categoria';
        $slug = $base;
        $suffix = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function create(): void
    {
        $name = trim($this->name);
        $icon = trim($this->icon);

        $this->resetErrorBag();

        if ($name === '') {
            $this->addError('name', 'El nombre es obligatorio.');

            return;
        }

        if ($this->nameExists($name, null)) {
            $this->addError('name', 'Ya existe una categoría con ese nombre.');

            return;
        }

        Category::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name, null),
            'icon' => $icon !== '' ? $icon : null,
        ]);

        $this->reset('name', 'icon');
        $this->success("Categoría \"{$name}\" creada.");
    }

    public function edit(Category $category): void
    {
        $this->editingId = $category->id;
        $this->editName = $category->name;
        $this->editIcon = (string) $category->icon;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function update(): void
    {
        $category = Category::findOrFail($this->editingId);

        $name = trim($this->editName);
        $icon = trim($this->editIcon);

        $this->resetErrorBag();

        if ($name === '') {
            $this->addError('editName', 'El nombre es obligatorio.');

            return;
        }

        if ($this->nameExists($name, $category->id)) {
            $this->addError('editName', 'Ya existe una categoría con ese nombre.');

            return;
        }

        $category->update([
            'name' => $name,
            'slug' => $this->uniqueSlug($name, $category->id),
            'icon' => $icon !== '' ? $icon : null,
        ]);

        $this->showEditModal = false;
        $this->success("Categoría \"{$name}\" actualizada.");
    }

    public function delete(Category $category): void
    {
        $name = $category->name;
        $category->delete();

        $this->success("\"{$name}\" fue eliminada. Sus locaciones quedaron sin categoría.");
    }

    public function render()
    {
        return view('livewire.admin.category-list', [
            'categories' => Category::withCount('locations')->orderBy('name')->get(),
        ]);
    }
}
