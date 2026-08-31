<div class="flex flex-col gap-6">

    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold">Manajemen Meja Cafe</flux:heading>
            <flux:subheading>Kelola data meja cafe</flux:subheading>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="w-full sm:w-auto">
            Tambah Meja
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari kode atau nama meja..."
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
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Kode</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Nama Meja</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Kapasitas</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->cafeTables as $table)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $table->code }}</td>
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $table->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $table->capacity }} orang</td>
                        <td class="px-4 py-3">
                            @php
                            $statusColors = ['available' => 'green', 'occupied' => 'yellow', 'reserved' => 'blue', 'inactive' => 'zinc'];
                            $statusLabels = ['available' => 'Tersedia', 'occupied' => 'Terisi', 'reserved' => 'Dipesan', 'inactive' => 'Nonaktif'];
                            @endphp
                            <flux:badge color="{{ $statusColors[$table->status] ?? 'zinc' }}" size="sm">
                                {{ $statusLabels[$table->status] ?? $table->status }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <flux:button
                                    wire:click="openEditModal({{ $table->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    title="Edit" />
                                <flux:button
                                    wire:click="openDeleteModal({{ $table->id }})"
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
                                <flux:icon name="table-cells" class="size-12 opacity-40" />
                                <p class="font-medium">
                                    @if ($search)
                                    Tidak ada meja yang cocok dengan pencarian "<span class="text-zinc-700 dark:text-zinc-300">{{ $search }}</span>"
                                    @else
                                    Belum ada data meja cafe
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->cafeTables->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $this->cafeTables->links() }}
        </div>
        @endif
    </div>

    <flux:text class="text-xs text-zinc-400">
        Total {{ $this->cafeTables->total() }} meja ditemukan
    </flux:text>

    <flux:modal wire:model="showFormModal" class="md:w-[560px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $cafeTableId ? 'Edit Meja' : 'Tambah Meja Baru' }}
                </flux:heading>
                <flux:subheading>
                    {{ $cafeTableId ? 'Perbarui data meja cafe.' : 'Isi form berikut untuk menambahkan meja baru.' }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="code"
                    label="Kode Meja"
                    placeholder="Contoh: T-01"
                    required
                    icon="hashtag" />

                <flux:input
                    wire:model="name"
                    label="Nama Meja"
                    placeholder="Contoh: Meja Teras 1"
                    required
                    icon="table-cells" />

                <flux:input
                    wire:model="capacity"
                    type="number"
                    label="Kapasitas"
                    placeholder="2"
                    required />

                <flux:select wire:model="status" label="Status" required>
                    <flux:select.option value="available">Tersedia</flux:select.option>
                    <flux:select.option value="occupied">Terisi</flux:select.option>
                    <flux:select.option value="reserved">Dipesan</flux:select.option>
                    <flux:select.option value="inactive">Nonaktif</flux:select.option>
                </flux:select>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showFormModal', false)" variant="ghost">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $cafeTableId ? 'Simpan Perubahan' : 'Tambah' }}
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
                <flux:heading size="lg" class="font-bold text-red-600 dark:text-red-400">Hapus Meja</flux:heading>
                <flux:subheading class="mt-1">
                    Anda akan menghapus meja <strong class="text-zinc-900 dark:text-white">{{ $deleteName }}</strong>.
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