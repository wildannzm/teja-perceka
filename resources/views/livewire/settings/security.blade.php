<section class="w-full">
 @include('partials.settings-heading')

 <div class="mt-6 max-w-xl">
 <h2 class="text-lg font-semibold text-zinc-900">Ubah Kata Sandi</h2>
 <p class="text-sm text-zinc-500 mt-1">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.</p>

 <form method="POST" wire:submit="updatePassword" class="mt-6 flex flex-col gap-5">

 <!-- Password Baru -->
 <div class="flex flex-col gap-1.5">
 <label for="new_password" class="text-sm font-medium text-zinc-700">Kata Sandi Baru</label>
 <input
 id="new_password"
 wire:model="password"
 type="password"
 required
 autocomplete="new-password"
 placeholder="Masukkan kata sandi baru"
 class="w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors @error('password') border-red-400 @enderror"
 >
 @error('password')
 <p class="text-xs text-red-600">{{ $message }}</p>
 @enderror
 </div>

 <!-- Konfirmasi Password -->
 <div class="flex flex-col gap-1.5">
 <label for="password_confirmation" class="text-sm font-medium text-zinc-700">Konfirmasi Kata Sandi</label>
 <input
 id="password_confirmation"
 wire:model="password_confirmation"
 type="password"
 required
 autocomplete="new-password"
 placeholder="Ulangi kata sandi baru"
 class="w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 focus:outline-none focus:border-brand-500 focus:ring-0 transition-colors"
 >
 </div>

 <!-- Notifikasi sukses -->
 @if (session('status') === 'password-updated')
 <div class="rounded-lg bg-brand-50 border border-brand-200 px-4 py-3 text-sm text-brand-700">
 ✓ Kata sandi berhasil diperbarui.
 </div>
 @endif

 <div class="flex items-center gap-3">
 <button
 type="submit"
 data-test="update-password-button"
 class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2"
 >
 Simpan Kata Sandi
 </button>
 </div>
 </form>
 </div>
</section>
