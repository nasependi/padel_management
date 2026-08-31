<?php

namespace App\Livewire\Cafe;

use App\Models\ProductCategory;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Kategori Produk')]
class ProductCategoryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $productCategoryId = null;

    public string $name = '';

    public bool $isActive = true;

    public int $sortOrder = 0;

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function productCategories()
    {
        return ProductCategory::query()
            ->withCount('products')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
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
        $category = ProductCategory::findOrFail($id);

        $this->productCategoryId = $category->id;
        $this->name = $category->name;
        $this->isActive = $category->is_active;
        $this->sortOrder = $category->sort_order;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'sortOrder.required' => 'Urutan wajib diisi.',
        ]);

        $isEdit = $this->productCategoryId !== null;
        $slug = Str::slug($this->name);
        $uniqueSlug = (string) Rule::unique('product_categories', 'slug')->ignore($this->productCategoryId);

        $data = [
            'name' => $this->name,
            'slug' => $slug,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($isEdit) {
            $category = ProductCategory::findOrFail($this->productCategoryId);
            $data['slug'] = ProductCategory::query()
                ->where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists() ? $slug . '-' . $category->id : $slug;
            $category->update($data);
            $message = 'Kategori berhasil diperbarui.';
        } else {
            $data['slug'] = ProductCategory::query()->where('slug', $slug)->exists()
                ? $slug . '-' . Str::random(5)
                : $slug;
            ProductCategory::create($data);
            $message = 'Kategori berhasil ditambahkan.';
        }

        $this->showFormModal = false;
        $this->resetForm();
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDeleteModal(int $id): void
    {
        $category = ProductCategory::findOrFail($id);
        $this->deleteId = $category->id;
        $this->deleteName = $category->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $category = ProductCategory::findOrFail($this->deleteId);

        if ($category->products()->exists()) {
            Flux::toast(variant: 'danger', text: 'Kategori tidak dapat dihapus karena masih memiliki produk.');
            $this->showDeleteModal = false;

            return;
        }

        $category->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
        Flux::toast(variant: 'success', text: 'Kategori berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->productCategoryId = null;
        $this->name = '';
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cafe.product-category-index')
            ->layout('layouts.app', ['title' => 'Manajemen Kategori Produk']);
    }
}
