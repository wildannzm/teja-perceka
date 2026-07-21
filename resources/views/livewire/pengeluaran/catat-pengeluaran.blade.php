<div class="flex flex-col gap-6 max-w-2xl mx-auto w-full pb-10">

 <div class="flex flex-col gap-1">
 <h1 class="text-2xl font-bold text-zinc-900">Catat Pengeluaran</h1>
 <p class="text-sm text-zinc-500">Pencatatan pengeluaran kas BUMDes ke Jurnal Umum secara otomatis.</p>
 </div>

 @error('submit')
 <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
 {{ $message }}
 </div>
 @enderror

 <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-6 flex flex-col gap-5">

 {{-- 1. Jenis Pengeluaran --}}
 <div>
 <label class="block text-sm font-semibold text-zinc-700 mb-2">Jenis Pengeluaran</label>
 <div class="grid grid-cols-2 gap-3">
 <label
 class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
 {{ $jenisPengeluaran === 'per_unit' ? 'border-brand-500 bg-brand-50 ' : 'border-zinc-200 hover:border-zinc-300' }}"
 >
 <input type="radio" wire:model.live="jenisPengeluaran" value="per_unit" class="sr-only">
 <div class="size-4 rounded-full border-2 flex items-center justify-center shrink-0
 {{ $jenisPengeluaran === 'per_unit' ? 'border-brand-500' : 'border-zinc-400' }}">
 @if ($jenisPengeluaran === 'per_unit')
 <div class="size-2 rounded-full bg-brand-500"></div>
 @endif
 </div>
 <div>
 <div class="text-sm font-semibold text-zinc-900">Per Unit Wisata</div>
 <div class="text-xs text-zinc-500">Terikat ke satu unit</div>
 </div>
 </label>

 <label
 class="flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
 {{ $jenisPengeluaran === 'umum_bumdes' ? 'border-brand-500 bg-brand-50 ' : 'border-zinc-200 hover:border-zinc-300' }}"
 >
 <input type="radio" wire:model.live="jenisPengeluaran" value="umum_bumdes" class="sr-only">
 <div class="size-4 rounded-full border-2 flex items-center justify-center shrink-0
 {{ $jenisPengeluaran === 'umum_bumdes' ? 'border-brand-500' : 'border-zinc-400' }}">
 @if ($jenisPengeluaran === 'umum_bumdes')
 <div class="size-2 rounded-full bg-brand-500"></div>
 @endif
 </div>
 <div>
 <div class="text-sm font-semibold text-zinc-900">Umum BUMDes</div>
 <div class="text-xs text-zinc-500">Gaji, BPJS, donasi, dll.</div>
 </div>
 </label>
 </div>
 </div>

 {{-- 2. Pilih Unit (kondisional) --}}
 @if ($jenisPengeluaran === 'per_unit')
 <div>
 <label for="unitWisataId" class="block text-sm font-semibold text-zinc-700 mb-1.5">Unit Wisata <span class="text-red-500">*</span></label>
 <select
 id="unitWisataId"
 wire:model="unitWisataId"
 class="w-full rounded-xl border-zinc-300 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error('unitWisataId') border-red-400 @enderror"
 >
 <option value="">— Pilih Unit Wisata —</option>
 @foreach ($units as $unit)
 <option value="{{ $unit->id }}">{{ $unit->nama }} ({{ $unit->kode }})</option>
 @endforeach
 </select>
 @error('unitWisataId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 @endif

 {{-- 3. Tanggal --}}
 <div>
 <label for="tanggal" class="block text-sm font-semibold text-zinc-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
 <input
 type="date"
 id="tanggal"
 wire:model="tanggal"
 class="w-full rounded-xl border-zinc-300 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error('tanggal') border-red-400 @enderror"
 >
 @error('tanggal') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>

 {{-- 4. Akun Biaya --}}
 <div>
 <label for="kodeAkunId" class="block text-sm font-semibold text-zinc-700 mb-1.5">Jenis Biaya (Akun) <span class="text-red-500">*</span></label>
 <select
 id="kodeAkunId"
 wire:model="kodeAkunId"
 class="w-full rounded-xl border-zinc-300 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error('kodeAkunId') border-red-400 @enderror"
 >
 <option value="">— Pilih Jenis Biaya —</option>
 @foreach ($akunBiaya as $akun)
 <option value="{{ $akun->id }}">{{ $akun->kode }} | {{ $akun->nama }}</option>
 @endforeach
 </select>
 @error('kodeAkunId') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>

 {{-- 5. Keterangan --}}
 <div>
 <label for="keterangan" class="block text-sm font-semibold text-zinc-700 mb-1.5">Keterangan <span class="text-red-500">*</span></label>
 <input
 type="text"
 id="keterangan"
 wire:model="keterangan"
 placeholder="cth: Gaji Karyawan Bulan Juli 2026"
 class="w-full rounded-xl border-zinc-300 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error('keterangan') border-red-400 @enderror"
 >
 @error('keterangan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>

 {{-- 6. Nominal --}}
 <div>
 <label for="nominal" class="block text-sm font-semibold text-zinc-700 mb-1.5">Nominal (Rp) <span class="text-red-500">*</span></label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
 <span class="text-zinc-500 text-sm font-medium">Rp</span>
 </div>
 <input
 type="number"
 id="nominal"
 wire:model="nominal"
 min="1"
 step="1000"
 placeholder="0"
 class="pl-10 w-full rounded-xl border-zinc-300 text-zinc-900 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 @error('nominal') border-red-400 @enderror"
 >
 </div>
 @error('nominal') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>

 {{-- Preview jurnal --}}
 @if ($nominal && $kodeAkunId && $keterangan)
 @php
 $selectedAkun = $akunBiaya->firstWhere('id', $kodeAkunId);
 @endphp
 @if ($selectedAkun)
 <div class="bg-zinc-50 rounded-xl border border-zinc-200 p-4">
 <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Preview Jurnal Umum</p>
 <div class="text-xs text-zinc-600 space-y-1 font-mono">
 <div class="flex justify-between gap-4">
 <span>Dr. {{ $selectedAkun->kode }} - {{ $selectedAkun->nama }}</span>
 <span>Rp {{ number_format((float)$nominal, 0, ',', '.') }}</span>
 </div>
 <div class="flex justify-between gap-4 pl-6 text-zinc-400">
 <span>Cr. 1-1100 - Kas</span>
 <span>Rp {{ number_format((float)$nominal, 0, ',', '.') }}</span>
 </div>
 </div>
 </div>
 @endif
 @endif

 {{-- Submit --}}
 <div class="flex gap-3 pt-1">
 <button
 wire:click="submit"
 wire:loading.attr="disabled"
 wire:target="submit"
 class="flex-1 py-2.5 rounded-xl bg-brand-300 hover:bg-brand-400 text-zinc-900 text-sm font-semibold transition-all active:scale-[0.98] disabled:opacity-70 flex justify-center items-center gap-2"
 >
 <span wire:loading.remove wire:target="submit">Simpan Pengeluaran</span>
 <span wire:loading wire:target="submit" class="flex items-center gap-2">
 <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 Menyimpan...
 </span>
 </button>
 </div>

 </div>
</div>
