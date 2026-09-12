<x-layouts::auth.split :title="__('Masuk')">
    <div class="flex flex-col gap-5">
        <div class="text-center md:text-left">
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl">Masuk ke Akun Anda</h1>
            <p class="mt-1 text-sm text-zinc-500">Masukkan email dan password untuk melanjutkan</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-center text-sm text-brand-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email" placeholder="email@example.com"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('email') border-red-400 bg-red-50 @enderror">
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5" x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-zinc-700">Kata Sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate
                            class="text-sm font-medium text-brand-600 hover:text-brand-700 hover:underline">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
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

            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                    class="size-4 cursor-pointer rounded border-zinc-300 text-brand-600 focus:ring-brand-500/30">
                <label for="remember" class="cursor-pointer select-none text-sm leading-none text-zinc-600">Ingat saya</label>
            </div>

            <button type="submit" data-test="login-button" :disabled="submitting"
                class="mt-1 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-500/30 active:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-75">
                <span x-show="!submitting">Masuk</span>
                <span x-show="submitting" x-cloak>Memproses...</span>
            </button>
        </form>
    </div>
</x-layouts::auth.split>
