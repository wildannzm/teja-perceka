<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Pengaturan Kata Sandi') }}</flux:heading>

    <x-settings.layout :heading="__('Ubah Kata Sandi')" :subheading="__('Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.')">
        <div class="my-6 w-full max-w-xl">
            <form method="POST" wire:submit="updatePassword" class="flex flex-col gap-5">
                
                <!-- New password -->
                <div class="flex flex-col gap-0.5" x-data="{ show: false }">
                    <label for="new_password" class="text-sm font-medium text-zinc-700">Kata Sandi Baru</label>
                    <div class="relative">
                        <input id="new_password" wire:model="password" :type="show ? 'text' : 'password'" required autocomplete="new-password"
                            placeholder="Masukkan kata sandi baru"
                            class="w-full bg-white shadow-sm rounded-xl border border-zinc-300 pl-3.5 pr-10 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('password') border-red-400 focus:border-red-400 focus:ring-red-400/20 @enderror">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 hover:text-zinc-600 focus:outline-none">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</p>
                    @error('password')
                        <p class="text-xs text-red-600 mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password confirmation -->
                <div class="flex flex-col gap-0.5" x-data="{ show: false }">
                    <label for="password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <input id="password_confirmation" wire:model="password_confirmation" :type="show ? 'text' : 'password'" required
                            autocomplete="new-password" placeholder="Ulangi kata sandi baru"
                            class="w-full bg-white shadow-sm rounded-xl border border-zinc-300 pl-3.5 pr-10 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 hover:text-zinc-600 focus:outline-none">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Success notification -->
                @if (session('status') === 'password-updated')
                    <div class="rounded-lg bg-brand-50 border border-brand-200 px-4 py-3 text-sm text-brand-700 font-medium flex items-center gap-2">
                        <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                        Kata sandi berhasil diperbarui.
                    </div>
                @endif

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" data-test="update-password-button"
                        class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 active:scale-95 shadow-sm">
                        Simpan Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </x-settings.layout>
</section>
