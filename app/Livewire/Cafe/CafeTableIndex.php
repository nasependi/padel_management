<?php

namespace App\Livewire\Cafe;

use App\Models\CafeTable;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Meja Cafe')]
class CafeTableIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $cafeTableId = null;

    public string $code = '';

    public string $name = '';

    public int $capacity = 2;

    public string $status = 'available';

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function cafeTables()
    {
        return CafeTable::query()
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
        $table = CafeTable::findOrFail($id);

        $this->cafeTableId = $table->id;
        $this->code = $table->code;
        $this->name = $table->name;
        $this->capacity = $table->capacity;
        $this->status = $table->status;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('cafe_tables', 'code')->ignore($this->cafeTableId)],
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:available,occupied,reserved,inactive'],
        ], [
            'code.required' => 'Kode meja wajib diisi.',
            'code.unique' => 'Kode meja sudah digunakan.',
            'name.required' => 'Nama meja wajib diisi.',
            'capacity.required' => 'Kapasitas wajib diisi.',
            'capacity.min' => 'Kapasitas minimal 1.',
        ]);

        $isEdit = $this->cafeTableId !== null;

        if ($isEdit) {
            $table = CafeTable::findOrFail($this->cafeTableId);
            $table->update($validated);
            $message = 'Meja berhasil diperbarui.';
        } else {
            CafeTable::create($validated);
            $message = 'Meja berhasil ditambahkan.';
        }

        $this->showFormModal = false;
        $this->resetForm();
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDeleteModal(int $id): void
    {
        $table = CafeTable::findOrFail($id);
        $this->deleteId = $table->id;
        $this->deleteName = $table->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $table = CafeTable::findOrFail($this->deleteId);
        $table->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
        Flux::toast(variant: 'success', text: 'Meja berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->cafeTableId = null;
        $this->code = '';
        $this->name = '';
        $this->capacity = 2;
        $this->status = 'available';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cafe.cafe-table-index')
            ->layout('layouts.app', ['title' => 'Manajemen Meja Cafe']);
    }
}
