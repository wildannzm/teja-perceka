<div x-data="{ editingUserId: @entangle('editingUserId') }" class="space-y-6">
    <x-page-header title="Manajemen Pengguna & Impersonasi" description="Kelola seluruh akun pengguna sistem BUMDes. Gunakan penyamaran (impersonate) untuk masuk ke akun terkait.">
        <x-slot:actions>
            <button type="button" wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-brand-600 border border-transparent rounded-xl hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 transition-colors shadow-sm">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah User
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Filters & Search -->
    <div class="bg-white p-4 border border-zinc-200 rounded-2xl flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="w-full md:w-80 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <svg class="size-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau email pengguna..."
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl pl-10 pr-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
        </div>

        <div class="w-full md:w-56">
            <select
                wire:model.live.debounce.250ms="selectedRole"
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl px-3.5 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ \App\Support\RoleLabels::label($role) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- User table (desktop) / cards (mobile) -->
    <div class="hidden md:block bg-white border border-zinc-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-500 uppercase text-[11px] font-semibold tracking-wider border-b border-zinc-200">
                    <tr>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Unit Wisata</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-zinc-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50/80 transition-colors" wire:key="user-row-{{ $user->id }}">
                            <td class="px-5 py-4">
                                <div class="min-w-0">
                                    <div class="font-bold text-zinc-900 truncate">{{ $user->name }}</div>
                                    <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ \App\Support\RoleLabels::label($user->getRoleNames()->first()) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-zinc-600">
                                    {{ $user->businessUnit ? $user->businessUnit->name : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($user->is_active ?? true)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-zinc-400">
                                        <span class="size-1.5 rounded-full bg-zinc-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(Auth::id() !== $user->id)
                                        <form method="POST" action="{{ route('impersonate.start', $user) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" title="Penyamaran (Impersonate)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl shadow-sm transition-all active:scale-95">
                                                <flux:icon icon="user-plus" class="size-3.5" />
                                                <span class="hidden xl:inline">Impersonate</span>
                                            </button>
                                        </form>
                                    @endif
                                    <button wire:click="editUser({{ $user->id }})" type="button" title="Edit"
                                        class="inline-flex items-center justify-center size-7 rounded-lg bg-amber-400 text-amber-950 transition-colors hover:bg-amber-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                            <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                            <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                        </svg>
                                    </button>
                                    @if(Auth::id() !== $user->id)
                                        <flux:button wire:click="confirmDelete({{ $user->id }})" variant="danger" size="xs" icon="trash" title="Hapus" />
                                    @else
                                        <span class="text-xs text-zinc-400 italic ml-1">Akun Anda</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-zinc-400 text-sm">
                                Tidak ada pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- User cards (mobile) -->
    <div class="flex flex-col gap-3 md:hidden">
        @forelse($users as $user)
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden" wire:key="user-card-{{ $user->id }}">
                <div class="px-4 py-3 flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-zinc-900 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-zinc-500 truncate">{{ $user->email }}</p>
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ \App\Support\RoleLabels::label($user->getRoleNames()->first()) }}
                            </span>
                            @if($user->is_active ?? true)
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-zinc-400">
                                    <span class="size-1.5 rounded-full bg-zinc-400"></span> Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-1.5 shrink-0">
                        <button wire:click="editUser({{ $user->id }})" type="button" title="Edit"
                            class="inline-flex items-center justify-center size-7 rounded-lg bg-amber-400 text-amber-950 transition-colors hover:bg-amber-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                            </svg>
                        </button>
                        @if(Auth::id() !== $user->id)
                            <flux:button wire:click="confirmDelete({{ $user->id }})" variant="danger" size="xs" icon="trash" title="Hapus" />
                        @endif
                    </div>
                </div>
                <div class="px-4 py-3 border-t border-zinc-100 flex items-center justify-between gap-2">
                    <p class="text-xs text-zinc-500 truncate">Unit: <span class="text-zinc-700 font-medium">{{ $user->businessUnit ? $user->businessUnit->name : '-' }}</span></p>
                    @if(Auth::id() !== $user->id)
                        <form method="POST" action="{{ route('impersonate.start', $user) }}" class="inline-block shrink-0">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl shadow-sm transition-all active:scale-95">
                                <flux:icon icon="user-plus" class="size-3.5" />
                                <span>Impersonate</span>
                            </button>
                        </form>
                    @else
                        <span class="text-xs text-zinc-400 italic shrink-0">Akun Anda</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border-2 border-dashed border-zinc-200 p-8 text-center">
                <p class="text-sm text-zinc-500">Tidak ada pengguna yang ditemukan.</p>
            </div>
        @endforelse
        @if ($users->hasPages())
            <div class="bg-white border border-zinc-200 rounded-2xl px-4 py-3 shadow-sm">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Edit modal -->
    <div x-show="editingUserId !== null" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editingUserId !== null" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 transition-opacity bg-zinc-900/50 backdrop-blur-sm" aria-hidden="true"
                @click="editingUserId = null; $wire.cancelEdit()"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editingUserId !== null" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-lg p-6 sm:p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-white border border-zinc-200/70 shadow-xl rounded-3xl relative z-10">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900" id="modal-title">Edit Pengguna</h2>
                        <p class="mt-0.5 text-sm text-zinc-500">Perbarui nama, email, hak akses, dan password.</p>
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
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                        <input id="email" type="email" wire:model="email" placeholder="email@contoh.com" required
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('email') border-red-400 @enderror">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_role" class="text-sm font-medium text-zinc-700">Hak Akses</label>
                            <select id="edit_role" wire:model.live="editRole" required
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-base text-zinc-900 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('editRole') border-red-400 @enderror">
                                <option value="">— Pilih hak akses —</option>
                                @foreach($allRoles as $role)
                                    <option value="{{ $role }}">{{ \App\Support\RoleLabels::label($role) }}</option>
                                @endforeach
                            </select>
                            @error('editRole') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium text-zinc-700">Status Akun</span>
                            <label class="inline-flex items-center gap-2.5 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="editIsActive" class="size-4 rounded accent-emerald-600">
                                <span class="text-sm text-zinc-700">{{ $editIsActive ? 'Aktif' : 'Nonaktif' }}</span>
                            </label>
                        </div>
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
                            @error('editBusinessUnitId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <div class="flex flex-col gap-4" x-data="{ showPassword: false, showConfirm: false }">
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_password" class="text-sm font-medium text-zinc-700">Password Baru <span class="font-normal text-zinc-400">(opsional)</span></label>
                            <div class="relative">
                                <input id="edit_password" :type="showPassword ? 'text' : 'password'" wire:model="editPassword" autocomplete="new-password"
                                    placeholder="Kosongkan jika tidak diubah"
                                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-4 pr-11 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none @error('editPassword') border-red-400 @enderror">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 hover:text-zinc-600 focus:outline-none" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <svg x-show="!showPassword" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('editPassword') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Password</label>
                            <div class="relative">
                                <input id="edit_password_confirmation" :type="showConfirm ? 'text' : 'password'" wire:model="editPassword_confirmation" autocomplete="new-password"
                                    placeholder="Ulangi password baru"
                                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-4 pr-11 py-3 text-base text-zinc-900 placeholder-zinc-400 focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/15 transition outline-none">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 hover:text-zinc-600 focus:outline-none" :aria-label="showConfirm ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <svg x-show="!showConfirm" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showConfirm" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-2">
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
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between gap-4 shrink-0">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900">Tambah User</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Buat akun pengguna baru beserta hak aksesnya.</p>
                    </div>
                    <button wire:click="closeCreate" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 transition-colors">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="storeUser" class="flex flex-col overflow-y-auto">
                <div class="px-6 py-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="flex flex-col gap-1.5">
                        <label for="create_name" class="text-sm font-medium text-zinc-700">Nama Lengkap</label>
                        <input id="create_name" type="text" wire:model="createName" placeholder="Masukkan nama..." required
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createName') border-red-400 @enderror">
                        @error('createName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="create_email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                        <input id="create_email" type="email" wire:model="createEmail" placeholder="email@contoh.com" required
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createEmail') border-red-400 @enderror">
                        @error('createEmail') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="create_role" class="text-sm font-medium text-zinc-700">Hak Akses</label>
                            <select id="create_role" wire:model.live="createRole" required
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors bg-white @error('createRole') border-red-400 @enderror">
                                <option value="">— Pilih hak akses —</option>
                                @foreach($allRoles as $role)
                                    <option value="{{ $role }}">{{ \App\Support\RoleLabels::label($role) }}</option>
                                @endforeach
                            </select>
                            @error('createRole') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-sm font-medium text-zinc-700">Status</span>
                            <label class="inline-flex items-center gap-2 rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm cursor-pointer select-none">
                                <input type="checkbox" wire:model.live="createIsActive" class="size-4 rounded accent-emerald-600">
                                <span class="text-zinc-700">{{ $createIsActive ? 'Aktif' : 'Nonaktif' }}</span>
                            </label>
                        </div>
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
                            @error('createBusinessUnitId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex flex-col gap-4" x-data="{ showPassword: false, showConfirm: false }">
                        <div class="flex flex-col gap-1.5">
                            <label for="create_password" class="text-sm font-medium text-zinc-700">Password</label>
                            <div class="relative">
                                <input id="create_password" :type="showPassword ? 'text' : 'password'" wire:model="createPassword" required autocomplete="new-password"
                                    placeholder="Min. 8 karakter"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 pl-3.5 pr-11 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('createPassword') border-red-400 @enderror">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600 focus:outline-none" :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <svg x-show="!showPassword" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            @error('createPassword') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="create_password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Password</label>
                            <div class="relative">
                                <input id="create_password_confirmation" :type="showConfirm ? 'text' : 'password'" wire:model="createPassword_confirmation" required autocomplete="new-password"
                                    placeholder="Ulangi password"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 pl-3.5 pr-11 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600 focus:outline-none" :aria-label="showConfirm ? 'Sembunyikan password' : 'Tampilkan password'">
                                    <svg x-show="!showConfirm" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showConfirm" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

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
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="size-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900">Hapus Pengguna?</h2>
                            <p class="text-sm text-zinc-500 mt-0.5">Data yang dihapus tidak dapat dikembalikan.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors disabled:opacity-60">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
