<div class="flex flex-col gap-6">

    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold">Manajemen Kategori Produk</flux:heading>
            <flux:subheading>Kelola kategori produk cafe</flux:subheading>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="w-full sm:w-auto">
            Tambah Kategori
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama kategori..."
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
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Nama Kategori</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Urutan</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Jumlah Produk</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->productCategories as $category)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                            <flux:badge color="blue" size="sm">{{ $category->products_count }} produk</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($category->is_active)
                            <flux:badge color="green" size="sm">Aktif</flux:badge>
                            @else
                            <flux:badge color="zinc" size="sm">Nonaktif</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <flux:button
                                    wire:click="openEditModal({{ $category->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    title="Edit" />
                                <flux:button
                                    wire:click="openDeleteModal({{ $category->id }})"
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
                        <td colspan="5" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="tag" class="size-12 opacity-40" />
                                <p class="font-medium">
                                    @if ($search)
                                    Tidak ada kategori yang cocok dengan pencarian "<span class="text-zinc-700 dark:text-zinc-300">{{ $search }}</span>"
                                    @else
                                    Belum ada data kategori produk
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->productCategories->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $this->productCategories->links() }}
        </div>
        @endif
    </div>

    <flux:text class="text-xs text-zinc-400">
        Total {{ $this->productCategories->total() }} kategori ditemukan
    </flux:text>

    <flux:modal wire:model="showFormModal" class="md:w-[560px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $productCategoryId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </flux:heading>
                <flux:subheading>
                    {{ $productCategoryId ? 'Perbarui data kategori produk.' : 'Isi form berikut untuk menambahkan kategori baru.' }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nama Kategori"
                    placeholder="Contoh: Minuman, Makanan Ringan"
                    required
                    icon="tag" />

                <flux:input
                    wire:model="sortOrder"
                    type="number"
                    label="Urutan Tampil"
                    placeholder="0"
                    required />

                <flux:switch wire:model="isActive" label="Status Aktif" />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showFormModal', false)" variant="ghost">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $productCategoryId ? 'Simpan Perubahan' : 'Tambah' }}
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
                <flux:heading size="lg" class="font-bold text-red-600 dark:text-red-400">Hapus Kategori</flux:heading>
                <flux:subheading class="mt-1">
                    Anda akan menghapus kategori <strong class="text-zinc-900 dark:text-white">{{ $deleteName }}</strong>.
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