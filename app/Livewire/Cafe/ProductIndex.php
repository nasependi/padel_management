<?php

namespace App\Livewire\Cafe;

use App\Models\Product;
use App\Models\ProductCategory;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Manajemen Produk')]
class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public bool $showFormModal = false;

    public bool $showDeleteModal = false;

    public ?int $productId = null;

    public ?int $productCategoryId = null;

    public string $sku = '';

    public string $name = '';

    public string $unit = 'pcs';

    public string $sellingPrice = '0';

    public string $costPrice = '0';

    public int $minimumStock = 0;

    public bool $trackStock = true;

    public bool $isActive = true;

    public string $description = '';

    public ?int $deleteId = null;

    public string $deleteName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            }))
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()->orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->productId = $product->id;
        $this->productCategoryId = $product->product_category_id;
        $this->sku = $product->sku;
        $this->name = $product->name;
        $this->unit = $product->unit;
        $this->sellingPrice = (string) $product->selling_price;
        $this->costPrice = (string) $product->cost_price;
        $this->minimumStock = $product->minimum_stock;
        $this->trackStock = $product->track_stock;
        $this->isActive = $product->is_active;
        $this->description = (string) $product->description;

        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'productCategoryId' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->productId)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'sellingPrice' => ['required', 'numeric', 'min:0'],
            'costPrice' => ['required', 'numeric', 'min:0'],
            'minimumStock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ], [
            'sku.required' => 'SKU wajib diisi.',
            'sku.unique' => 'SKU sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'sellingPrice.required' => 'Harga jual wajib diisi.',
            'costPrice.required' => 'Harga modal wajib diisi.',
        ]);

        $data = [
            'product_category_id' => $validated['productCategoryId'],
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'selling_price' => $validated['sellingPrice'],
            'cost_price' => $validated['costPrice'],
            'minimum_stock' => $validated['minimumStock'],
            'track_stock' => $this->trackStock,
            'is_active' => $this->isActive,
            'description' => $validated['description'],
        ];

        $isEdit = $this->productId !== null;

        if ($isEdit) {
            $product = Product::findOrFail($this->productId);
            $product->update($data);
            $message = 'Produk berhasil diperbarui.';
        } else {
            Product::create($data);
            $message = 'Produk berhasil ditambahkan.';
        }

        $this->showFormModal = false;
        $this->resetForm();
        Flux::toast(variant: 'success', text: $message);
    }

    public function openDeleteModal(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->deleteId = $product->id;
        $this->deleteName = $product->name;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $product = Product::findOrFail($this->deleteId);
        $product->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = '';
        Flux::toast(variant: 'success', text: 'Produk berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->productId = null;
        $this->productCategoryId = null;
        $this->sku = '';
        $this->name = '';
        $this->unit = 'pcs';
        $this->sellingPrice = '0';
        $this->costPrice = '0';
        $this->minimumStock = 0;
        $this->trackStock = true;
        $this->isActive = true;
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cafe.product-index')
            ->layout('layouts.app', ['title' => 'Manajemen Produk']);
    }
}
