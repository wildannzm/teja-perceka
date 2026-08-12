<x-layouts::auth :title="__('Masuk')">
    <div class="flex flex-col gap-5">
        <div class="text-center">
            <h1 class="text-xl font-bold text-zinc-900">Masuk ke Akun Anda</h1>
            <p class="text-sm text-zinc-500 mt-1">Masukkan email dan password</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="rounded-lg bg-brand-50 border border-brand-200 px-4 py-3 text-sm text-brand-700 text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <!-- Email -->
            <div class="flex flex-col gap-1.5">
                <label for="email" class="min-h-0 min-w-0 text-sm font-medium text-zinc-700">Alamat Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email" placeholder="email@example.com"
                    class="w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1.5" x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="min-h-0 min-w-0 text-sm font-medium text-zinc-700">Kata Sandi</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate
                            class="text-sm text-brand-600 hover:text-brand-700 hover:underline">
                            Lupa kata sandi?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                        placeholder="Kata Sandi"
                        class="w-full rounded-xl border-2 border-brand-500 pl-3.5 pr-10 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors @error('password') border-red-400 @enderror">
                    
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
                @error('password')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                    class="m-0 p-0 size-4 rounded border-zinc-300 text-brand-500 focus:ring-brand-400 cursor-pointer">
                <label for="remember" class="min-h-0 min-w-0 text-sm leading-none text-zinc-600 cursor-pointer select-none">Ingat saya</label>
            </div>

            <!-- Submit -->
            <button type="submit" data-test="login-button" :disabled="submitting"
                class="w-full flex items-center justify-center py-3 px-4 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-semibold text-base rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 disabled:opacity-75 disabled:cursor-not-allowed">
                <span x-show="!submitting">Masuk</span>
                <span x-show="submitting" x-cloak>Masuk...</span>
            </button>
        </form>
    </div>
</x-layouts::auth>
