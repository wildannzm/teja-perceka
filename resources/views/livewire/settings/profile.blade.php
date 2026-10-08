<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Pengaturan Profil') }}</flux:heading>

    <x-settings.layout :heading="__('Profil')" :subheading="__('Perbarui nama dan alamat email Anda')">
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-col gap-2 p-5 bg-white border border-zinc-200 rounded-xl shadow-sm">
                <div class="text-sm font-medium text-zinc-500">Nama Lengkap</div>
                <div class="text-lg font-semibold text-brand-900">{{ auth()->user()->name }}</div>
            </div>

            <div class="flex flex-col gap-2 p-5 bg-white border border-zinc-200 rounded-xl shadow-sm">
                <div class="text-sm font-medium text-zinc-500">Alamat Email</div>
                <div class="text-lg font-semibold text-brand-900 break-all">{{ auth()->user()->email }}</div>

                @if ($this->hasUnverifiedEmail)
                    <div class="mt-2 text-sm text-zinc-600">
                        {{ __('Email Anda belum terverifikasi.') }}
                        <button type="button" wire:click="resendVerificationNotification"
                            class="font-semibold text-brand-700 hover:text-brand-800 underline underline-offset-2">
                            {{ __('Kirim ulang email verifikasi') }}
                        </button>
                    </div>
                @endif
            </div>

            @hasanyrole('super_admin|kepala_desa|pengawas')
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="openProfileModal"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-brand-600 border border-transparent rounded-xl hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 transition-colors">
                        Edit Profil
                    </button>
                </div>
            @endhasanyrole

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2 p-5 bg-brand-50 border border-brand-100 rounded-xl shadow-sm">
                    <div class="text-sm font-medium text-brand-700">Hak Akses</div>
                    <div class="text-base font-semibold text-brand-900 uppercase">
                        {{ \App\Support\RoleLabels::label(auth()->user()->roles->first()?->name) }}
                    </div>
                </div>

                @if (auth()->user()->businessUnit)
                    <div class="flex flex-col gap-2 p-5 bg-brand-50 border border-brand-100 rounded-xl shadow-sm">
                        <div class="text-sm font-medium text-brand-700">Unit Usaha</div>
                        <div class="text-base font-semibold text-brand-900">
                            {{ auth()->user()->businessUnit->name }}
                        </div>
                    </div>
                @endif
            </div>

            <div
                class="p-4 bg-zinc-50 rounded-xl border border-zinc-200 text-sm text-zinc-600 mt-6 flex items-start gap-3">
                <svg class="size-5 text-zinc-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                @hasanyrole('super_admin|kepala_desa|pengawas')
                    <p>Perubahan email akan meminta verifikasi ulang. Silakan ke menu "Keamanan" untuk mengganti password.</p>
                @else
                    <p>Data profil di atas hanya dapat diubah oleh administrator atau direktur terkait. Anda tidak diizinkan
                        mengubah identitas dasar secara mandiri. Silakan ke menu "Security" untuk mengganti password.</p>
                @endhasanyrole
            </div>
        </div>

    </x-settings.layout>

    @if ($showProfileModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Edit Profil">
            <button type="button" wire:click="closeProfileModal" aria-label="Tutup" class="absolute inset-0 cursor-default bg-white/70 backdrop-blur-[3px]"></button>
            <div class="relative w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl">
                <button type="button" wire:click="closeProfileModal" aria-label="Tutup"
                    class="absolute top-4 right-4 flex size-8 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-700 focus:outline-none">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
                <form wire:submit="updateProfileInformation" class="flex flex-col gap-5">
                    <div class="text-center">
                        <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6 0 3.375 3.375 0 0 1 6 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </span>
                        <h3 class="mt-3 text-lg font-semibold text-zinc-900">Edit Profil</h3>
                        <p class="mt-1 text-sm text-zinc-500">Perbarui nama dan alamat email Anda.</p>
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <label for="modal_profile_name" class="text-sm font-medium text-zinc-700">Nama Lengkap</label>
                        <input id="modal_profile_name" wire:model="name" type="text" required autocomplete="name"
                            placeholder="Nama lengkap"
                            class="w-full bg-white shadow-sm rounded-xl border border-zinc-300 pl-3.5 pr-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('name') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror">
                        @error('name')
                            <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-0.5">
                        <label for="modal_profile_email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                        <input id="modal_profile_email" wire:model="email" type="email" required autocomplete="email"
                            placeholder="email@contoh.com"
                            class="w-full bg-white shadow-sm rounded-xl border border-zinc-300 pl-3.5 pr-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('email') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror">
                        @error('email')
                            <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closeProfileModal" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 font-semibold text-sm rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white hover:bg-brand-700 font-semibold text-sm rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:bg-brand-800">
                            <span wire:loading.remove wire:target="updateProfileInformation">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateProfileInformation">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
