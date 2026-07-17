<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Kelola Profil Akun</h1>
        </div>

        <!-- Search and List -->
        <div class="flex flex-col gap-4 bg-white dark:bg-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700">
            
            <div class="flex justify-between items-center mb-4">
                <div class="w-1/3">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email..." icon="magnifying-glass" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400">
                    <thead class="text-xs text-neutral-700 uppercase bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-300">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Lengkap</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Peran (Role)</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr class="bg-white border-b dark:bg-neutral-900 dark:border-neutral-700">
                            <td class="px-6 py-4 font-medium text-neutral-900 dark:text-white">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $user->roles->pluck('name')->join(', ') ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <flux:button size="sm" variant="subtle" wire:click="editUser({{ $user->id }})">Edit Profil</flux:button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Tidak ada data ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <flux:modal wire:model="editingUserId" class="w-full max-w-lg">
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Edit Profil Pengguna</h2>
            
            <form wire:submit="saveUser" class="flex flex-col gap-4">
                <flux:input wire:model="name" label="Nama Lengkap" placeholder="Masukkan nama..." required />
                <flux:input wire:model="email" type="email" label="Alamat Email" placeholder="email@contoh.com" required />
                
                <div class="flex justify-end gap-2 mt-4">
                    <flux:button variant="subtle" wire:click="cancelEdit">Batal</flux:button>
                    <flux:button variant="primary" type="submit">Simpan Perubahan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
