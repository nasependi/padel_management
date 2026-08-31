<?php

namespace App\Livewire\Cafe;

use App\Models\Supplier;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Supplier')]
class SupplierIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $supplierId = null;

    public string $code = '';

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public bool $isActive = true;

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function suppliers()
    {
        return Supplier::query()
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            }))
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
        $supplier = Supplier::findOrFail($id);

        $this->supplierId = $supplier->id;
        $this->code = $supplier->code;
        $this->name = $supplier->name;
        $this->phone = (string) $supplier->phone;
        $this->email = (string) $supplier->email;
        $this->address = (string) $supplier->address;
        $this->isActive = $supplier->is_active;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('suppliers', 'code')->ignore($this->supplierId)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode supplier wajib diisi.',
            'code.unique' => 'Kode supplier sudah digunakan.',
            'name.required' => 'Nama supplier wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);
        $validated['is_active'] = $this->isActive;

        $isEdit = $this->supplierId !== null;

        if ($isEdit) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update($validated);
            $message = 'Supplier berhasil diperbarui.';
        } else {
            Supplier::create($validated);
            $message = 'Supplier berhasil ditambahkan.';
        }

        $this->showFormModal = false;
        $this->resetForm();
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDeleteModal(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->deleteId = $supplier->id;
        $this->deleteName = $supplier->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $supplier = Supplier::findOrFail($this->deleteId);
        $supplier->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
        Flux::toast(variant: 'success', text: 'Supplier berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->supplierId = null;
        $this->code = '';
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cafe.supplier-index')
            ->layout('layouts.app', ['title' => 'Manajemen Supplier']);
    }
}
