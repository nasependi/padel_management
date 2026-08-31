<?php

namespace App\Livewire\Settings;

use App\Models\Permission;
use App\Models\Role;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Role')]
class RoleIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $roleId = null;

    public string $name = '';

    /** @var array<int, int> */
    public array $selectedPermissions = [];

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount('permissions')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()->orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);

        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->all();

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->roleId)],
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah digunakan.',
        ]);

        $isEdit = $this->roleId !== null;

        if ($isEdit) {
            $role = Role::findOrFail($this->roleId);
            $role->update($validated);
        } else {
            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        }

        // Checkbox values arrive as strings; cast to int so Spatie resolves them by id, not by name.
        $role->syncPermissions(array_map('intval', $this->selectedPermissions));

        $this->showFormModal = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: $isEdit ? 'Role berhasil diperbarui.' : 'Role berhasil ditambahkan.');
    }

    public function openDeleteModal(int $id): void
    {
        $role = Role::findOrFail($id);
        $this->deleteId = $role->id;
        $this->deleteName = $role->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $role = Role::findOrFail($this->deleteId);

        if ($role->users()->exists()) {
            Flux::toast(variant: 'danger', text: 'Role tidak dapat dihapus karena masih digunakan oleh user.');
            $this->showDeleteModal = false;

            return;
        }

        $role->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';

        Flux::toast(variant: 'success', text: 'Role berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->roleId = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.role-index')
            ->layout('layouts.app', ['title' => 'Manajemen Role']);
    }
}
