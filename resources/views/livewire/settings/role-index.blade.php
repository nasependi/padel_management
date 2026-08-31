<div class="flex flex-col gap-6">

    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold">Manajemen Role</flux:heading>
            <flux:subheading>Kelola role beserta hak akses (permission) pengguna</flux:subheading>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="w-full sm:w-auto">
            Tambah Role
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama role..."
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
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Nama Role</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Jumlah Permission</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->roles as $role)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $role->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">
                            <flux:badge color="blue" size="sm">{{ $role->permissions_count }} permission</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <flux:button
                                    wire:click="openEditModal({{ $role->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    title="Edit" />
                                <flux:button
                                    wire:click="openDeleteModal({{ $role->id }})"
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
                        <td colspan="3" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="shield-check" class="size-12 opacity-40" />
                                <p class="font-medium">
                                    @if ($search)
                                    Tidak ada role yang cocok dengan pencarian "<span class="text-zinc-700 dark:text-zinc-300">{{ $search }}</span>"
                                    @else
                                    Belum ada data role
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->roles->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $this->roles->links() }}
        </div>
        @endif
    </div>

    <flux:text class="text-xs text-zinc-400">
        Total {{ $this->roles->total() }} role ditemukan
    </flux:text>

    <flux:modal wire:model="showFormModal" class="md:w-[680px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $roleId ? 'Edit Role' : 'Tambah Role Baru' }}
                </flux:heading>
                <flux:subheading>
                    {{ $roleId ? 'Perbarui nama dan hak akses role.' : 'Isi form berikut untuk menambahkan role baru.' }}
                </flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nama Role"
                    placeholder="Contoh: admin, kasir, manager"
                    required
                    icon="shield-check" />

                <div>
                    <flux:label>Permission</flux:label>
                    <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded-lg border border-zinc-200 p-3 sm:grid-cols-2 dark:border-zinc-700">
                        @forelse ($this->permissions as $permission)
                        <flux:checkbox
                            wire:model="selectedPermissions"
                            value="{{ $permission->id }}"
                            label="{{ $permission->name }}" />
                        @empty
                        <p class="text-sm text-zinc-400">Belum ada data permission.</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showFormModal', false)" variant="ghost">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $roleId ? 'Simpan Perubahan' : 'Tambah' }}
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
            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950">
                <flux:icon name="trash" class="size-6 text-red-600 dark:text-red-400" />
            </div>
            <div>
                <flux:heading size="lg" class="font-bold text-red-600 dark:text-red-400">Hapus Role</flux:heading>
                <flux:subheading class="mt-1">
                    Anda akan menghapus role <strong class="text-zinc-900 dark:text-white">{{ $deleteName }}</strong>.
                    Tindakan ini tidak dapat dibatalkan.
                </flux:subheading>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-2">
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