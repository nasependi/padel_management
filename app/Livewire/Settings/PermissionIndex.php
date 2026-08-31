<?php

namespace App\Livewire\Settings;

use App\Models\Permission;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Permission')]
class PermissionIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $permissionId = null;

    public string $name = '';

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->withCount('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $permission = Permission::findOrFail($id);

        $this->permissionId = $permission->id;
        $this->name = $permission->name;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->ignore($this->permissionId)],
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique' => 'Nama permission sudah digunakan.',
        ]);

        $isEdit = $this->permissionId !== null;

        if ($isEdit) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update($validated);
        } else {
            Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);
        }

        $this->showFormModal = false;
        $this->resetForm();

        Flux::toast(variant: 'success', text: $isEdit ? 'Permission berhasil diperbarui.' : 'Permission berhasil ditambahkan.');
    }

    public function openDeleteModal(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $this->deleteId = $permission->id;
        $this->deleteName = $permission->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $permission = Permission::findOrFail($this->deleteId);

        if ($permission->roles()->exists()) {
            Flux::toast(variant: 'danger', text: 'Permission tidak dapat dihapus karena masih digunakan oleh role.');
            $this->showDeleteModal = false;

            return;
        }

        $permission->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';

        Flux::toast(variant: 'success', text: 'Permission berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->permissionId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.settings.permission-index')
            ->layout('layouts.app', ['title' => 'Manajemen Permission']);
    }
}
