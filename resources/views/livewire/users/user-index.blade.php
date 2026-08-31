<div class="flex flex-col gap-6">

    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" class="font-bold">Manajemen User</flux:heading>
            <flux:subheading>Kelola data pengguna sistem padel</flux:subheading>
        </div>
        <flux:button wire:click="openCreateModal" variant="primary" size="sm" icon="plus" class="w-full sm:w-auto">
            Tambah User
        </flux:button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama atau email..."
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
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">
                            <button wire:click="sort('id')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                #
                                @if ($sortField === 'id')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                @else
                                <flux:icon name="chevrons-up-down" class="size-3 opacity-40" />
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">
                            <button wire:click="sort('name')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                Nama
                                @if ($sortField === 'name')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                @else
                                <flux:icon name="chevrons-up-down" class="size-3 opacity-40" />
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">
                            <button wire:click="sort('email')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                Email
                                @if ($sortField === 'email')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                @else
                                <flux:icon name="chevrons-up-down" class="size-3 opacity-40" />
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">
                            <button wire:click="sort('email_verified_at')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                Status
                                @if ($sortField === 'email_verified_at')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                @else
                                <flux:icon name="chevrons-up-down" class="size-3 opacity-40" />
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">
                            <button wire:click="sort('created_at')" class="inline-flex items-center gap-1 hover:text-zinc-900 dark:hover:text-white">
                                Dibuat
                                @if ($sortField === 'created_at')
                                <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="size-3" />
                                @else
                                <flux:icon name="chevrons-up-down" class="size-3 opacity-40" />
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-600 dark:text-zinc-300">Role</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->users as $user)
                    <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $user->id }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar
                                    :name="$user->name"
                                    :initials="$user->initials()"
                                    size="sm"
                                    class="shrink-0" />
                                <div>
                                    <p class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                                    @if ($user->id === auth()->id())
                                    <span class="text-xs text-blue-500 font-medium">(Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-300">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if ($user->email_verified_at)
                            <flux:badge color="green" size="sm" icon="check-circle">Terverifikasi</flux:badge>
                            @else
                            <flux:badge color="yellow" size="sm" icon="clock">Belum Verifikasi</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400 text-xs">
                            {{ $user->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->roles->isNotEmpty())
                            <flux:badge color="indigo" size="sm">{{ $user->roles->first()->name }}</flux:badge>
                            @else
                            <span class="text-xs text-zinc-400">Tanpa role</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <flux:button
                                    wire:click="openEditModal({{ $user->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    title="Edit" />
                                <flux:button
                                    wire:click="openDeleteModal({{ $user->id }})"
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    title="Hapus"
                                    class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950"
                                    :disabled="$user->id === auth()->id()" />
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-zinc-400 dark:text-zinc-500">
                                <flux:icon name="users" class="size-12 opacity-40" />
                                <p class="font-medium">
                                    @if ($search)
                                    Tidak ada user yang cocok dengan pencarian "<span class="text-zinc-700 dark:text-zinc-300">{{ $search }}</span>"
                                    @else
                                    Belum ada data user
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($this->users->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            {{ $this->users->links() }}
        </div>
        @endif
    </div>

    {{-- Total info --}}
    <flux:text class="text-xs text-zinc-400">
        Total {{ $this->users->total() }} user ditemukan
    </flux:text>


    <flux:modal wire:model="showFormModal" class="md:w-[680px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-bold">
                    {{ $userId ? 'Edit User' : 'Tambah User Baru' }}
                </flux:heading>
                <flux:subheading>
                    {{ $userId ? 'Perbarui data pengguna sistem.' : 'Isi form berikut untuk menambahkan user baru.' }}
                </flux:subheading>
            </div>

            {{-- Form --}}
            <form wire:submit="save" class="space-y-4">
                <flux:input
                    wire:model="name"
                    label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap"
                    required
                    icon="user" />

                <flux:input
                    wire:model="email"
                    type="email"
                    label="Alamat Email"
                    placeholder="user@example.com"
                    required
                    icon="envelope" />

                <flux:input
                    wire:model="password"
                    type="password"
                    label="{{ $userId ? 'Password Baru' : 'Password' }}"
                    placeholder="{{ $userId ? 'Kosongkan jika tidak ingin mengubah' : 'Min. 8 karakter' }}"
                    :required="!$userId"
                    icon="lock-closed"
                    viewable />

                <flux:input
                    wire:model="password_confirmation"
                    type="password"
                    label="Konfirmasi Password"
                    placeholder="Ulangi password"
                    :required="!$userId || $password !== ''"
                    icon="lock-closed"
                    viewable />

                <flux:select wire:model.live="roleId" label="Role" placeholder="Pilih role...">
                    @foreach ($this->roles as $role)
                    <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div>
                    <flux:label>Permission dari Role</flux:label>
                    <div class="mt-2 flex flex-wrap gap-1.5 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        @forelse ($this->selectedRolePermissions as $permissionName)
                        <flux:badge color="blue" size="sm">{{ $permissionName }}</flux:badge>
                        @empty
                        <p class="text-sm text-zinc-400">Pilih role untuk melihat permission yang dimiliki.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="$set('showFormModal', false)" variant="ghost">
                        Batal
                    </flux:button>
                    <flux:button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $userId ? 'Simpan Perubahan' : 'Tambah' }}
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
                <flux:heading size="lg" class="font-bold text-red-600 dark:text-red-400">Hapus User</flux:heading>
                <flux:subheading class="mt-1">
                    Anda akan menghapus user <strong class="text-zinc-900 dark:text-white">{{ $deleteName }}</strong>.
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