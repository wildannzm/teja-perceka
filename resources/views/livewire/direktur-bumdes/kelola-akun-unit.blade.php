<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-10">

    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-zinc-900">Kelola Akun Kepala Unit</h1>
        <p class="text-sm text-zinc-500">Kelola nama, assignment unit, password, dan status aktif untuk semua akun Kepala
            Unit.</p>
    </div>


    {{-- Daftar User Kepala Unit --}}
    <div class="flex flex-col gap-4">
        @forelse ($users as $user)
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                {{-- Info baris utama --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-5">
                    {{-- Avatar & Info --}}
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="size-10 rounded-full bg-brand-100 flex items-center justify-center shrink-0">
                            <span
                                class="text-sm font-bold text-brand-700">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-zinc-900 truncate">{{ $user->name }}</div>
                            <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
                            <div class="text-xs mt-0.5 text-zinc-400">
                                Unit: <span
                                    class="font-medium text-zinc-600">{{ $user->unitWisata?->nama ?? '— tidak di-assign —' }}</span>
                            </div>
                        </div>
                    </div>



                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button wire:click="startEdit({{ $user->id }})"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-2 border-brand-500 text-zinc-700 hover:bg-zinc-100 :bg-zinc-800 transition-colors">
                            <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path
                                    d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" />
                                <path
                                    d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" />
                            </svg>
                            Edit
                        </button>

                    </div>
                </div>

                {{-- Panel Edit (collapsible) --}}
                @if ($editingUserId === $user->id)
                    <div class="border-t border-zinc-200 bg-zinc-50 px-5 py-4">
                        <p class="text-sm font-semibold text-zinc-700 mb-3">Edit Data Akun</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nama Lengkap</label>
                                <input type="text" wire:model="editName"
                                    class="block w-full px-3.5 py-2.5 bg-white border-2 border-zinc-200 rounded-xl text-zinc-900 text-sm placeholder-zinc-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 hover:border-zinc-300 transition-all outline-none shadow-sm"
                                    placeholder="Masukkan nama lengkap">
                                @error('editName')
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Unit Usaha</label>
                                <select wire:model="editUnitWisataId"
                                    class="block w-full pl-3.5 pr-10 py-2.5 bg-[position:right_0.875rem_center] bg-white border-2 border-zinc-200 rounded-xl text-zinc-900 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 hover:border-zinc-300 transition-all outline-none shadow-sm">
                                    <option value="">— Tidak Di-assign —</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                                    @endforeach
                                </select>
                                @error('editUnitWisataId')
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 mb-1.5">Password Baru (Opsional)</label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" wire:model="editPassword"
                                        class="block w-full pl-3.5 pr-10 py-2.5 bg-white border-2 border-zinc-200 rounded-xl text-zinc-900 text-sm placeholder-zinc-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 hover:border-zinc-300 transition-all outline-none shadow-sm"
                                        placeholder="Kosongkan jika tidak ingin mengubah password">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400 hover:text-zinc-600 focus:outline-none">
                                        <svg x-show="!show" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg x-show="show" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                                @error('editPassword')
                                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex gap-2 mt-5">
                            <button wire:click="saveEdit"
                                class="px-5 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white text-sm font-semibold transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">Simpan Perubahan</button>
                            <button wire:click="cancelEdit"
                                class="px-5 py-2.5 rounded-xl border border-zinc-200 bg-white text-zinc-700 text-sm font-semibold hover:bg-zinc-50 hover:border-zinc-300 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2">Batal</button>
                        </div>
                    </div>
                @endif


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
</div>
