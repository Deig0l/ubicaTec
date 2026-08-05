<div>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Categorías</h1>
            <p class="text-sm text-base-content/60">Agrupa las locaciones del campus. Al eliminar una categoría, sus locaciones quedan sin categoría (no se borran).</p>
        </div>

        <x-mary-button label="Ver locaciones" icon="o-building-office-2" link="{{ route('admin.locations') }}" no-wire-navigate class="btn-ghost btn-sm" />
    </div>

    {{-- Alta de categoría --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-4">
            <form wire:submit="create" class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="w-full sm:w-28">
                    <x-mary-input label="Ícono" wire:model="icon" placeholder="🏛️" maxlength="8" />
                </div>
                <div class="flex-1">
                    <x-mary-input label="Nombre" wire:model="name" placeholder="Ej. Biblioteca" icon="o-tag" />
                </div>
                <div class="sm:pt-8">
                    <x-mary-button label="Agregar" type="submit" icon="o-plus" class="btn-primary w-full sm:w-auto" spinner="create" />
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de categorías --}}
    <div class="overflow-x-auto rounded-box border border-base-300 bg-base-100">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-16">Ícono</th>
                    <th>Nombre</th>
                    <th class="w-40">Locaciones</th>
                    <th class="w-28 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr wire:key="category-{{ $category->id }}">
                        <td class="text-2xl">{{ $category->icon ?: '—' }}</td>
                        <td class="font-medium">{{ $category->name }}</td>
                        <td>
                            <span class="badge badge-ghost">{{ $category->locations_count }} {{ $category->locations_count === 1 ? 'locación' : 'locaciones' }}</span>
                        </td>
                        <td>
                            <div class="flex gap-1 justify-end">
                                <x-mary-button
                                    icon="o-pencil-square"
                                    wire:click="edit({{ $category->id }})"
                                    class="btn-ghost btn-sm"
                                    tooltip="Renombrar"
                                />
                                <x-mary-button
                                    icon="o-trash"
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="¿Eliminar &quot;{{ $category->name }}&quot;? Sus {{ $category->locations_count }} locación(es) quedarán sin categoría."
                                    class="btn-ghost btn-sm text-error"
                                    tooltip="Eliminar"
                                />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-base-content/50">
                            Aún no hay categorías. Agrega la primera arriba.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal de edición --}}
    <x-mary-modal wire:model="showEditModal" title="Editar categoría" separator>
        <form wire:submit="update" class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row gap-3 sm:items-start">
                <div class="w-full sm:w-28">
                    <x-mary-input label="Ícono" wire:model="editIcon" placeholder="🏛️" maxlength="8" />
                </div>
                <div class="flex-1">
                    <x-mary-input label="Nombre" wire:model="editName" icon="o-tag" />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-2">
                <x-mary-button label="Cancelar" @click="$wire.showEditModal = false" class="btn-ghost" />
                <x-mary-button label="Guardar" type="submit" icon="o-check-circle" class="btn-primary" spinner="update" />
            </div>
        </form>
    </x-mary-modal>
</div>
