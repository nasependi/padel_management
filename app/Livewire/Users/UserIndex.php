<?php

namespace App\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;
    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $roleId = null;

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    #[Computed]
    public function selectedRolePermissions()
    {
        if (! $this->roleId) {
            return collect();
        }

        return Role::query()->find($this->roleId)?->permissions->pluck('name') ?? collect();
    }

    public function sort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $user = User::with('roles')->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->roleId = $user->roles->first()?->id;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $isEdit = $this->userId !== null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
        ];

        if (! $isEdit || $this->password !== '') {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $this->validate($rules, [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($isEdit) {
            $user = User::findOrFail($this->userId);
            $data = ['name' => $this->name, 'email' => $this->email];
            if ($this->password !== '') {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
            $message = 'User berhasil diperbarui.';
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $message = 'User berhasil ditambahkan.';
        }

        $user->syncRoles($this->roleId ? [$this->roleId] : []);

        $this->showFormModal = false;
        $this->resetForm();
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDeleteModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->deleteId = $user->id;
        $this->deleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deleteId === auth()->id()) {
            Flux::toast(variant: 'danger', text: 'Tidak dapat menghapus akun yang sedang digunakan.');
            $this->showDeleteModal = false;

            return;
        }

        $user = User::findOrFail($this->deleteId);
        $user->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
        Flux::toast(variant: 'success', text: 'User berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->roleId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.users.user-index')
            ->layout('layouts.app', ['title' => 'Manajemen User']);
    }
}
