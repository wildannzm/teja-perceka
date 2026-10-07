<x-layouts::auth.split :title="__('Konfirmasi Kata Sandi')">
    <div class="flex flex-col gap-5">
        <div class="text-center">
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </span>
            <h1 class="mt-3 text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl">Konfirmasi Kata Sandi</h1>
            <p class="mt-1 text-sm text-zinc-500">Area aman. Masukkan kata sandi untuk melanjutkan ke pengaturan keamanan.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-4" x-data="{ show: false, submitting: false }" @submit="submitting = true">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-sm font-medium text-zinc-700">Kata Sandi</label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autofocus autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-zinc-200 bg-zinc-50 py-2.5 pl-3.5 pr-11 text-base text-zinc-900 placeholder-zinc-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('password') border-red-400 bg-red-50 @enderror">
                    <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 transition hover:text-zinc-600 focus:outline-none">
                        <svg x-show="!show" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="show" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" data-test="confirm-password-button" :disabled="submitting"
                class="mt-1 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-500/30 active:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-75">
                <span x-show="!submitting">Konfirmasi</span>
                <span x-show="submitting" x-cloak>Memproses...</span>
            </button>
        </form>
    </div>
</x-layouts::auth.split>
