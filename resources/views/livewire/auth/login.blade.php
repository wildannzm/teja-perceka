<x-layouts::auth :title="__('Masuk')">
 <div class="flex flex-col gap-5">
 <div class="text-center">
 <h1 class="text-xl font-bold text-zinc-900">Masuk ke Akun Anda</h1>
 <p class="text-sm text-zinc-500 mt-1">Masukkan email dan password untuk melanjutkan</p>
 </div>

 <!-- Session Status -->
 @if (session('status'))
 <div class="rounded-lg bg-brand-50 border border-brand-200 px-4 py-3 text-sm text-brand-700 text-center">
 {{ session('status') }}
 </div>
 @endif

 <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4">
 @csrf

 <!-- Email -->
 <div class="flex flex-col gap-1.5">
 <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
 <input
 id="email"
 name="email"
 type="email"
 value="{{ old('email') }}"
 required
 autofocus
 autocomplete="email"
 placeholder="email@example.com"
 class="w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors @error('email') border-red-400 @enderror"
 >
 @error('email')
 <p class="text-xs text-red-600">{{ $message }}</p>
 @enderror
 </div>

 <!-- Password -->
 <div class="flex flex-col gap-1.5">
 <div class="flex items-center justify-between">
 <label for="password" class="text-sm font-medium text-zinc-700">Kata Sandi</label>
 @if (Route::has('password.request'))
 <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-brand-600 hover:text-brand-700 hover:underline">
 Lupa kata sandi?
 </a>
 @endif
 </div>
 <input
 id="password"
 name="password"
 type="password"
 required
 autocomplete="current-password"
 placeholder="Kata Sandi"
 class="w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors @error('password') border-red-400 @enderror"
 >
 @error('password')
 <p class="text-xs text-red-600">{{ $message }}</p>
 @enderror
 </div>

 <!-- Remember Me -->
 <div class="flex items-center gap-2">
 <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
 class="size-4 rounded border-zinc-300 text-brand-500 focus:ring-brand-400 cursor-pointer">
 <label for="remember" class="text-sm text-zinc-600 cursor-pointer">Ingat saya</label>
 </div>

 <!-- Submit -->
 <button
 type="submit"
 data-test="login-button"
 class="w-full py-3 px-4 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-semibold text-base rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2"
 >
 Masuk
 </button>
 </form>
 </div>
</x-layouts::auth>
