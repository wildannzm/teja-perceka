<div x-data="{ editingUserId: @entangle('editingUserId') }">
    <div class="flex h-full w-full flex-col gap-6">
        <x-page-header title="Kelola Hak Akses" />

        <!-- Search & list -->
        <div class="flex flex-col gap-4 bg-white p-4 sm:p-6 rounded-3xl shadow-[0_1px_2px_rgb(16,24,40,0.05),0_16px_40px_-16px_rgb(16,24,40,0.12)] border border-zinc-200/70">

            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-6">
                <div class="w-full md:w-1/2 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="size-6 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama atau email pengguna..."
                        class="pl-12 block w-full rounded-xl border border-zinc-200 bg-zinc-50 text-zinc-900 text-base py-3 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none">
                </div>
                <button type="button" wire:click="openCreate"
                    class="inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-brand-600 border border-transparent rounded-xl hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 transition-colors shrink-0">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah User
                </button>
            </div>

            <div class="hidden md:block overflow-x-auto rounded-xl border border-zinc-200">
                <table class="w-full text-sm text-left text-zinc-600">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Lengkap</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Hak Akses</th>
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($users as $user)
                            <tr class="bg-white hover:bg-zinc-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-zinc-900">
                                    {{ $user->name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium bg-brand-50 text-brand-700 rounded-full border border-brand-200">
                                        {{ \App\Support\RoleLabels::label($user->roles->first()?->name) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-row justify-center gap-1.5 items-center">
                                        <button wire:click="editUser({{ $user->id }})"
                                            type="button"
                                            style="background-color:#fbbf24;color:#1c1917;"
                                            title="Edit"
                                            class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                                <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                                <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                            </svg>
                                        </button>
                                        <flux:button wire:click="confirmDelete({{ $user->id }})"
                                            variant="danger" size="xs" icon="trash" title="Hapus" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
                                    <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                    Tidak ada data pengguna ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 md:hidden">
                @forelse($users as $user)
                    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden" wire:key="user-card-{{ $user->id }}">
                        <div class="bg-brand-50/60 border-b border-brand-100 px-4 py-3 flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-800 truncate">{{ $user->name }}</p>
                                <span class="inline-block mt-1 text-[11px] font-medium bg-brand-100 text-brand-800 px-2 py-0.5 rounded-md">
                                    {{ str($user->roles->first()?->name ?? 'User')->replace('_', ' ')->title() }}
                                </span>
                            </div>
                            <div class="flex gap-1.5 shrink-0 mt-0.5">
                                <button wire:click="editUser({{ $user->id }})"
                                    type="button"
                                    style="background-color:#fbbf24;color:#1c1917;"
                                    title="Edit"
                                    class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                        <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                        <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                    </svg>
                                </button>
                                <flux:button wire:click="confirmDelete({{ $user->id }})"
                                    variant="danger" size="xs" icon="trash" title="Hapus" />
                            </div>
                        </div>
                        <div class="px-4 py-3">
                            <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-0.5">Email</p>
                            <p class="text-sm text-zinc-800 break-all">{{ $user->email }}</p>
                        </div>
                        @if ($user->businessUnit)
                            <div class="px-4 py-3 border-t border-zinc-100">
                                <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-0.5">Unit Usaha</p>
                                <p class="text-sm text-zinc-800">{{ $user->businessUnit->name }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border-2 border-dashed border-zinc-200 p-8 text-center">
                        <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <p class="text-sm text-zinc-500">Tidak ada data pengguna ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Edit modal (pure Tailwind) -->
    <div x-show="editingUserId !== null" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Overlay -->
            <div x-show="editingUserId !== null" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-zinc-900/50 backdrop-blur-sm" aria-hidden="true"
                @click="editingUserId = null; $wire.cancelEdit()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="editingUserId !== null" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-lg p-6 sm:p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border border-zinc-200/70 shadow-[0_1px_2px_rgb(16,24,40,0.05),0_16px_40px_-16px_rgb(16,24,40,0.12)] rounded-3xl relative z-10">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900" id="modal-title">Edit Profil Pengguna</h2>
                        <p class="mt-1 text-sm text-zinc-500">Perbarui nama, email, hak akses, dan password pengguna.</p>
                    </div>
                    <button type="button" wire:click="cancelEdit" aria-label="Tutup"
                        class="flex size-9 shrink-0 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveUser" class="flex flex-col gap-5">

                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-sm font-medium text-zinc-700">Nama Lengkap</label>
                        <input id="name" type="text" wire:model="name" placeholder="Masukkan nama..." required
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('name') border-red-400 @enderror">
                        @error('name')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                        <input id="email" type="email" wire:model="email" placeholder="email@contoh.com" required
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('email') border-red-400 @enderror">
                        @error('email')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="edit_role" class="text-sm font-medium text-zinc-700">Hak Akses</label>
                        <select id="edit_role" wire:model.live="editRole" required
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('editRole') border-red-400 @enderror">
                            <option value="">— Pilih hak akses —</option>
                            <option value="direktur_bumdes">Direktur BUMDes</option>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="kepala_unit">Kepala Unit</option>
                        </select>
                        @error('editRole')
                            <span class="text-xs text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($editRole === 'kepala_unit')
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_unit" class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                            <select id="edit_unit" wire:model="editBusinessUnitId"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('editBusinessUnitId') border-red-400 @enderror">
                                <option value="">— Pilih unit usaha —</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('editBusinessUnitId')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_password" class="text-sm font-medium text-zinc-700">Password Baru <span class="font-normal text-zinc-400">(opsional)</span></label>
                            <input id="edit_password" type="password" wire:model="editPassword" autocomplete="new-password"
                                placeholder="Kosongkan jika tidak diubah"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('editPassword') border-red-400 @enderror">
                            @error('editPassword')
                                <span class="text-xs text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Password</label>
                            <input id="edit_password_confirmation" type="password" wire:model="editPassword_confirmation" autocomplete="new-password"
                                placeholder="Ulangi password baru"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-4">
                        <button type="button" wire:click="cancelEdit"
                            class="px-4 py-2.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-xl hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 transition-colors flex items-center gap-2"
                            wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveUser">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveUser">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Create user modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="closeCreate" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden z-10 max-h-[90vh]">

                {{-- Modal header --}}
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="size-9 rounded-full bg-brand-100 flex items-center justify-center shrink-0">
                            <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900">Tambah User</h2>
                            <p class="text-xs text-zinc-500 mt-0.5">Buat akun pengguna baru beserta hak aksesnya.</p>
                        </div>
                    </div>
                    <button wire:click="closeCreate" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 transition-colors">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal body + footer --}}
                <form wire:submit="storeUser" class="flex flex-col overflow-y-auto">
                <div class="px-6 py-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="flex flex-col gap-1.5">
                        <label for="create_name" class="text-sm font-medium text-zinc-700">Nama Lengkap</label>
                        <input id="create_name" type="text" wire:model="createName" placeholder="Masukkan nama..." required
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createName') border-red-400 @enderror">
                        @error('createName')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="create_email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                        <input id="create_email" type="email" wire:model="createEmail" placeholder="email@contoh.com" required
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createEmail') border-red-400 @enderror">
                        @error('createEmail')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="create_password" class="text-sm font-medium text-zinc-700">Password</label>
                            <input id="create_password" type="password" wire:model="createPassword" required autocomplete="new-password"
                                placeholder="Min. 8 karakter"
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createPassword') border-red-400 @enderror">
                            @error('createPassword')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="create_password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Password</label>
                            <input id="create_password_confirmation" type="password" wire:model="createPassword_confirmation" required autocomplete="new-password"
                                placeholder="Ulangi password"
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="create_role" class="text-sm font-medium text-zinc-700">Hak Akses</label>
                        <select id="create_role" wire:model.live="createRole" required
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors bg-white @error('createRole') border-red-400 @enderror">
                            <option value="">— Pilih hak akses —</option>
                            <option value="direktur_bumdes">Direktur BUMDes</option>
                            <option value="sekretaris">Sekretaris</option>
                            <option value="bendahara">Bendahara</option>
                            <option value="kepala_unit">Kepala Unit</option>
                        </select>
                        @error('createRole')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($createRole === 'kepala_unit')
                        <div class="flex flex-col gap-1.5">
                            <label for="create_unit" class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                            <select id="create_unit" wire:model="createBusinessUnitId"
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors bg-white @error('createBusinessUnitId') border-red-400 @enderror">
                                <option value="">— Pilih unit usaha —</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('createBusinessUnitId')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                {{-- Modal footer --}}
                <div class="px-6 py-4 border-t border-zinc-100 flex justify-end gap-2 shrink-0">
                    <button type="button" wire:click="closeCreate"
                        class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="storeUser"
                        class="px-5 py-2 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="storeUser">Simpan</span>
                        <span wire:loading wire:target="storeUser">Menyimpan...</span>
                    </button>
                </div>
                </form>

            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">Konfirmasi Hapus</h2>
                        <div class="mt-2 text-sm text-zinc-600">
                            <p>Yakin ingin menghapus data ini?</p>
                            <p>Data yang dihapus tidak dapat dikembalikan.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
