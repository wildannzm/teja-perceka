<div class="flex flex-col gap-6 max-w-5xl mx-auto w-full pb-10">

 <div class="flex flex-col gap-1">
 <h1 class="text-2xl font-bold text-zinc-900">Kelola Akun Kepala Unit</h1>
 <p class="text-sm text-zinc-500">Kelola nama, assignment unit, password, dan status aktif untuk semua akun Kepala Unit.</p>
 </div>

 {{-- Generated Password Alert (sekali tampil) --}}
 @if ($generatedPassword)
 <div class="p-4 bg-amber-50 border border-amber-300 rounded-2xl flex flex-col gap-2" role="alert">
 <div class="flex items-center gap-2 font-semibold text-amber-800">
 <svg class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
 Password Baru Telah Dibuat — Catat dan Sampaikan ke Kepala Unit!
 </div>
 <p class="text-sm text-amber-700">Password ini hanya ditampilkan <strong>sekali</strong>. Setelah halaman direfresh, password tidak bisa dilihat lagi.</p>
 <div class="mt-1 flex items-center gap-3 bg-white border border-amber-200 rounded-xl px-4 py-3">
 <code class="text-lg font-bold font-mono text-zinc-900 tracking-wider flex-1">{{ $generatedPassword }}</code>
 <button
 onclick="navigator.clipboard.writeText('{{ $generatedPassword }}').then(() => this.textContent = 'Tersalin!')"
 class="text-xs font-semibold text-brand-600 hover:text-brand-800 transition-colors shrink-0"
 >Salin</button>
 </div>
 <button wire:click="cancelResetPassword" class="text-xs text-amber-600 underline mt-1 text-left">Tutup pesan ini</button>
 </div>
 @endif

 {{-- Daftar User Kepala Unit --}}
 <div class="flex flex-col gap-4">
 @forelse ($users as $user)
 <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
 {{-- Info baris utama --}}
 <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-5">
 {{-- Avatar & Info --}}
 <div class="flex items-center gap-3 flex-1 min-w-0">
 <div class="size-10 rounded-full bg-brand-100 flex items-center justify-center shrink-0">
 <span class="text-sm font-bold text-brand-700">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
 </div>
 <div class="min-w-0">
 <div class="font-semibold text-zinc-900 truncate">{{ $user->name }}</div>
 <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
 <div class="text-xs mt-0.5 text-zinc-400">
 Unit: <span class="font-medium text-zinc-600">{{ $user->unitWisata?->nama ?? '— tidak di-assign —' }}</span>
 </div>
 </div>
 </div>

 {{-- Status Badge --}}
 <div class="flex items-center gap-2 shrink-0">
 @if ($user->is_active)
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-100 text-brand-700">
 <span class="size-1.5 rounded-full bg-brand-500"></span> Aktif
 </span>
 @else
 <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
 <span class="size-1.5 rounded-full bg-red-500"></span> Nonaktif
 </span>
 @endif
 </div>

 {{-- Action Buttons --}}
 <div class="flex flex-wrap items-center gap-2 shrink-0">
 <button wire:click="startEdit({{ $user->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-2 border-brand-500 text-zinc-700 hover:bg-zinc-100 :bg-zinc-800 transition-colors">
 <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z" /><path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z" /></svg>
 Edit
 </button>
 <button wire:click="startResetPassword({{ $user->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-amber-300 text-amber-700 hover:bg-amber-50 :bg-amber-900/20 transition-colors">
 <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" /></svg>
 Reset Password
 </button>
 <button
 wire:click="toggleStatus({{ $user->id }})"
 wire:confirm="{{ $user->is_active ? 'Nonaktifkan akun ini? User tidak akan bisa login.' : 'Aktifkan kembali akun ini?' }}"
 class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border transition-colors {{ $user->is_active ? 'border-red-300 text-red-600 hover:bg-red-50 :bg-red-900/20' : 'border-brand-300 text-brand-600 hover:bg-brand-50 :bg-emerald-900/20' }}"
 >
 @if ($user->is_active)
 <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zm2.25 8.5a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5z" clip-rule="evenodd" /></svg>
 Nonaktifkan
 @else
 <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
 Aktifkan
 @endif
 </button>
 </div>
 </div>

 {{-- Panel Edit (collapsible) --}}
 @if ($editingUserId === $user->id)
 <div class="border-t border-zinc-200 bg-zinc-50 px-5 py-4">
 <p class="text-sm font-semibold text-zinc-700 mb-3">Edit Data Akun</p>
 <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
 <div>
 <label class="block text-xs font-medium text-zinc-600 mb-1">Nama Lengkap</label>
 <input type="text" wire:model="editName"
 class="w-full rounded-xl border-2 border-brand-500 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none"
 placeholder="Nama lengkap"
 >
 @error('editName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-xs font-medium text-zinc-600 mb-1">Unit Wisata</label>
 <select wire:model="editUnitWisataId"
 class="w-full rounded-xl border-2 border-brand-500 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none"
 >
 <option value="">— Tidak Di-assign —</option>
 @foreach ($units as $unit)
 <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
 @endforeach
 </select>
 @error('editUnitWisataId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 </div>
 <div class="flex gap-2 mt-4">
 <button wire:click="saveEdit" class="px-4 py-2 rounded-xl bg-brand-300 hover:bg-brand-400 text-zinc-900 text-sm font-semibold transition-colors">Simpan</button>
 <button wire:click="cancelEdit" class="px-4 py-2 rounded-xl border border-2 border-brand-500 text-zinc-700 text-sm font-semibold hover:bg-zinc-100 :bg-zinc-700 transition-colors">Batal</button>
 </div>
 </div>
 @endif

 {{-- Panel Reset Password (collapsible) --}}
 @if ($resetPasswordUserId === $user->id && ! $generatedPassword)
 <div class="border-t border-zinc-200 bg-amber-50/50 px-5 py-4">
 <p class="text-sm font-semibold text-amber-700 mb-1">Reset Password Akun Ini</p>
 <p class="text-xs text-amber-600 mb-3">Password baru akan di-<em>generate</em> secara acak dan ditampilkan di sini untuk disampaikan ke Kepala Unit terkait.</p>
 <div class="flex gap-2">
 <button wire:click="generatePassword" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-colors">Generate Password Baru</button>
 <button wire:click="cancelResetPassword" class="px-4 py-2 rounded-xl border border-2 border-brand-500 text-zinc-700 text-sm font-semibold hover:bg-zinc-100 :bg-zinc-700 transition-colors">Batal</button>
 </div>
 </div>
 @endif

 </div>
 @empty
 <div class="bg-zinc-50 rounded-2xl border border-zinc-200 p-12 text-center">
 <svg class="size-12 mx-auto mb-3 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
 <p class="text-zinc-600 font-medium">Belum ada akun Kepala Unit yang terdaftar.</p>
 </div>
 @endforelse
 </div>
</div>
