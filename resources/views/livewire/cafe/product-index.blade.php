<div class="flex flex-col gap-6">

    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold">Manajemen Produk</flux:heading>
            <flux:subheading>Kelola data produk cafe</flux:subheading>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="w-full sm:w-auto">
            Tambah Produk
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama atau SKU..."
            icon="magnifying-glass"
            class="w-full sm:max-w-xs"
            clearable />
        <div class="flex items-center gap-2">
            <flux:text class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">Tampilkan:</flux:text>
            <flux:select wire:model.live="perPage" class="w-20">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">SKU</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Nama Produk</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Harga Jual</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Stok Min.</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->products as $product)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                            @if ($product->category)
                            <flux:badge color="indigo" size="sm">{{ $product->category->name }}</flux:badge>
                            @else
                            <span class="text-xs text-zinc-400">Tanpa kategori</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $product->minimum_stock }} {{ $product->unit }}</td>
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                            <flux:badge color="green" size="sm">Aktif</flux:badge>
                            @else
                            <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <flux:button
                                    wire:click="openEditModal({{ $product->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    title="Edit" />
                                <flux:button
                                    wire:click="openDeleteModal({{ $product->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    title="Hapus"
                                    class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="cube" class="size-12 opacity-40" />
                                <p class="font-medium">
                                    @if ($search)
                                    Tidak ada produk yang cocok dengan pencarian "<span class="text-zinc-700 dark:text-zinc-300">{{ $search }}</span>"
                                    @else
                                    Belum ada data produk
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->products->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $this->products->links() }}
        </div>
        @endif
    </div>

    <flux:text class="text-xs text-zinc-400">
        Total {{ $this->products->total() }} produk ditemukan
    </flux:text>

    <flux:modal wire:model="showFormModal" class="md:w-[680px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $productId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </flux:heading>
                <flux:subheading>
                    {{ $productId ? 'Perbarui data produk cafe.' : 'Isi form berikut untuk menambahkan produk baru.' }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input
                        wire:model="sku"
                        label="SKU"
                        placeholder="Contoh: MNM-001"
                        required
                        icon="hashtag" />

                    <flux:select wire:model="productCategoryId" label="Kategori" placeholder="Pilih kategori...">
                        @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input
                    wire:model="name"
                    label="Nama Produk"
                    placeholder="Masukkan nama produk"
                    required
                    icon="cube" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:input
                        wire:model="unit"
                        label="Satuan"
                        placeholder="pcs" />

                    <flux:input
                        wire:model="sellingPrice"
                        type="number"
                        step="0.01"
                        label="Harga Jual"
                        placeholder="0"
                        required />

                    <flux:input
                        wire:model="costPrice"
                        type="number"
                        step="0.01"
                        label="Harga Modal"
                        placeholder="0"
                        required />
                </div>

                <flux:input
                    wire:model="minimumStock"
                    type="number"
                    label="Stok Minimum"
                    placeholder="0" />

                <flux:textarea
                    wire:model="description"
                    label="Deskripsi"
                    placeholder="Deskripsi produk (opsional)" />

                <div class="flex flex-col gap-3 sm:flex-row sm:gap-8">
                    <flux:switch wire:model="trackStock" label="Lacak Stok" />
                    <flux:switch wire:model="isActive" label="Status Aktif" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showFormModal', false)" variant="ghost">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $productId ? 'Simpan Perubahan' : 'Tambah' }}
                        </span>
                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="md:w-[420px]">
        <div class="flex items-start gap-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950">
                <flux:icon name="trash" class="size-4 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <flux:heading size="lg" class="font-bold text-red-600 dark:text-red-400">Hapus Produk</flux:heading>
                <flux:subheading class="mt-1">
                    Anda akan menghapus produk <strong class="text-zinc-900 dark:text-white">{{ $deleteName }}</strong>.
                    Tindakan ini tidak dapat dibatalkan.
                </flux:subheading>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <flux:button type="button" wire:click="$set('showDeleteModal', false)" variant="ghost">
                Batal
            </flux:button>
            <flux:button
                wire:click="delete"
                variant="danger"
                size="sm"
                wire:loading.attr="disabled"
                icon="trash">
                <span wire:loading.remove wire:target="delete">Hapus</span>
                <span wire:loading wire:target="delete">Menghapus...</span>
            </flux:button>
        </div>
    </flux:modal>

</div>