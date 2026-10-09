<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-10">

    <x-page-header title="Kelola Akun Kepala Unit" description="Kelola nama, email, assignment unit, dan password untuk semua akun Kepala Unit.">
        <x-slot:actions>
            <button type="button" wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 bg-brand-600 text-white hover:bg-brand-700 font-semibold text-sm rounded-xl transition-colors shadow-sm">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Akun
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Unit head user list --}}
    <div class="flex flex-col gap-4">
        @forelse ($users as $user)
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden" wire:key="unit-account-{{ $user->id }}">
                {{-- Primary row info --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-5">
                    {{-- Avatar & info --}}
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="min-w-0">
                            <div class="font-semibold text-zinc-900 truncate">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
                            <div class="text-xs mt-0.5 text-zinc-400">
                                Unit: <span
                                    class="font-medium text-zinc-600">{{ $user->businessUnit?->name ?? '— Pilih Unit Usaha —' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex flex-row justify-end sm:justify-center gap-1.5 items-center shrink-0 w-full sm:w-auto">
                        <button wire:click="openEdit({{ $user->id }})"
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
            </div>
        @empty
            <div class="bg-zinc-50 rounded-2xl border border-zinc-200 p-12 text-center">
                <svg class="size-12 mx-auto mb-3 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <p class="text-zinc-600 font-medium">Belum ada akun Kepala Unit yang terdaftar.</p>
            </div>
        @endforelse
    </div>

    {{-- Create modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="$wire.closeCreate()">
            <div wire:click="closeCreate" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden z-10 max-h-[90vh]">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50">
                    <h2 class="text-base font-semibold text-zinc-900">Tambah Akun</h2>
                    <button wire:click="closeCreate" class="size-8 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 transition-colors">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="storeUser" class="px-6 py-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="createName" placeholder="Masukkan nama lengkap" autofocus
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('createName') border-red-400 @enderror">
                        @error('createName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="createEmail" placeholder="email@contoh.com"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('createEmail') border-red-400 @enderror">
                        @error('createEmail') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                        <select wire:model="createBusinessUnitId"
                            class="w-full max-w-full truncate rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer @error('createBusinessUnitId') border-red-400 @enderror">
                            <option value="">— Pilih Unit Usaha —</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('createBusinessUnitId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5" x-data="{ showPassword: false, showConfirm: false }">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-zinc-700">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" wire:model="createPassword" placeholder="Min. 8 karakter" autocomplete="new-password"
                                    class="w-full rounded-xl border-2 border-zinc-200 pl-3.5 pr-11 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('createPassword') border-red-400 @enderror">
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
                            <label class="text-sm font-medium text-zinc-700">Konfirmasi Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" wire:model="createPassword_confirmation" placeholder="Ulangi password" autocomplete="new-password"
                                    class="w-full rounded-xl border-2 border-zinc-200 pl-3.5 pr-11 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none">
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
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeCreate" class="flex-1 py-2.5 rounded-xl border-2 border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="storeUser" class="flex-1 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="storeUser">Tambah Akun</span>
                            <span wire:loading wire:target="storeUser">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="$wire.closeModal()">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden z-10 max-h-[90vh]">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50">
                    <h2 class="text-base font-semibold text-zinc-900">Edit Akun</h2>
                    <button wire:click="closeModal" class="size-8 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 transition-colors">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="saveEdit" class="px-6 py-5 flex flex-col gap-4 overflow-y-auto">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="editName" placeholder="Masukkan nama lengkap" autofocus
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('editName') border-red-400 @enderror">
                        @error('editName') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="editEmail" placeholder="email@contoh.com"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('editEmail') border-red-400 @enderror">
                        @error('editEmail') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                        <select wire:model="editBusinessUnitId"
                            class="w-full max-w-full truncate rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer @error('editBusinessUnitId') border-red-400 @enderror">
                            <option value="">— Pilih Unit Usaha —</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('editBusinessUnitId') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col gap-1.5" x-data="{ showPassword: false, showConfirm: false }">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-zinc-700">Password Baru <span class="font-normal text-zinc-400">(opsional)</span></label>
                            <div class="relative">
                                <input :type="showPassword ? 'text' : 'password'" wire:model="editPassword" placeholder="Kosongkan jika tetap" autocomplete="new-password"
                                    class="w-full rounded-xl border-2 border-zinc-200 pl-3.5 pr-11 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('editPassword') border-red-400 @enderror">
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
                            @error('editPassword') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-zinc-700">Konfirmasi Password</label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" wire:model="editPassword_confirmation" placeholder="Ulangi password" autocomplete="new-password"
                                    class="w-full rounded-xl border-2 border-zinc-200 pl-3.5 pr-11 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none">
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
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeModal" class="flex-1 py-2.5 rounded-xl border-2 border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveEdit" class="flex-1 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveEdit">Menyimpan...</span>
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
                            <p>Yakin ingin menghapus akun ini?</p>
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
