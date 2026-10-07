<div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm" wire:cloak x-data="{ showRecoveryCodes: false }">
    <div class="flex items-start gap-4">
        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-700">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <h4 class="text-base font-semibold text-zinc-900">Kode Pemulihan 2FA</h4>
            <p class="text-sm text-zinc-500 mt-0.5">Kode cadangan untuk masuk jika HP atau aplikasi authenticator hilang. Simpan di tempat aman.</p>
            <div class="mt-2 rounded-xl bg-zinc-50 border border-zinc-200 px-4 py-3 text-xs text-zinc-600 leading-relaxed">
                Klik Lihat kode pemulihan, salin semua kode, lalu simpan di pengelola kata sandi atau catatan aman. Satu kode hanya bisa dipakai satu kali.
            </div>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                <button type="button" x-show="!showRecoveryCodes" @click="showRecoveryCodes = true;" aria-expanded="false" aria-controls="recovery-codes-section"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-brand-600 text-white hover:bg-brand-700 font-semibold text-sm rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 active:bg-brand-800">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Lihat kode pemulihan
                </button>

                <button type="button" x-show="showRecoveryCodes" @click="showRecoveryCodes = false" aria-expanded="true" aria-controls="recovery-codes-section"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 font-semibold text-sm rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    Sembunyikan kode
                </button>

                @if (filled($recoveryCodes))
                    <button type="button" x-show="showRecoveryCodes" wire:click="regenerateRecoveryCodes"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-zinc-300 text-zinc-700 hover:bg-zinc-50 font-semibold text-sm rounded-xl transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Buat ulang kode
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div x-show="showRecoveryCodes" x-transition id="recovery-codes-section" class="relative overflow-hidden mt-4"
        x-bind:aria-hidden="!showRecoveryCodes">
        <div class="space-y-3">
            @error('recoveryCodes')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ $message }}</div>
            @enderror

            @if (filled($recoveryCodes))
                <div class="grid gap-1 p-4 font-mono text-sm rounded-xl bg-zinc-100 border border-zinc-200 text-zinc-800" role="list"
                    aria-label="Kode pemulihan">
                    @foreach ($recoveryCodes as $code)
                        <div role="listitem" class="select-text" wire:loading.class="opacity-50 animate-pulse">
                            {{ $code }}
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-zinc-500">
                    Setiap kode hanya bisa dipakai satu kali dan hangus setelah dipakai. Jika habis, klik Buat ulang kode di atas.
                </p>
            @endif
        </div>
    </div>
</div>
