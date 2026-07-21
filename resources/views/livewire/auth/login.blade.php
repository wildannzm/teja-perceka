<x-layouts::auth :title="__('Log in')">
 <div class="flex flex-col gap-5">
 <x-auth-header :title="__('Masuk ke Akun Anda')" :description="__('Masukkan email dan password untuk melanjutkan')" />

 <!-- Session Status -->
 <x-auth-session-status class="text-center" :status="session('status')" />

 <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
 @csrf

 <!-- Email Address -->
 <flux:input
 name="email"
 :label="__('Alamat Email')"
 :value="old('email')"
 type="email"
 required
 autofocus
 autocomplete="email"
 placeholder="email@example.com"
 class="text-base"
 />

 <!-- Password -->
 <div class="flex flex-col gap-1">
 <div class="flex items-center justify-between">
 <flux:label for="password">{{ __('Password') }}</flux:label>
 @if (Route::has('password.request'))
 <flux:link class="text-sm" :href="route('password.request')" wire:navigate>
 {{ __('Lupa password?') }}
 </flux:link>
 @endif
 </div>
 <flux:input
 id="password"
 name="password"
 type="password"
 required
 autocomplete="current-password"
 :placeholder="__('Password')"
 viewable
 class="text-base"
 />
 </div>

 <!-- Remember Me -->
 <input type="hidden" name="remember" value="1">

 <!-- Submit — tinggi eksplisit 52px agar mudah disentuh di HP -->
 <flux:button
 variant="primary"
 type="submit"
 class="w-full !min-h-[52px] text-base font-semibold"
 data-test="login-button"
 >
 {{ __('Masuk') }}
 </flux:button>
 </form>
 </div>
</x-layouts::auth>
