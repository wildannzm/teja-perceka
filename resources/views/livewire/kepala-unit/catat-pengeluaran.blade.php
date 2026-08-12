<div class="flex flex-col gap-6 max-w-2xl mx-auto w-full pb-20">

    {{-- Header --}}
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-zinc-900">Catat Pengeluaran</h1>
        <p class="text-sm text-zinc-500">
            Pencatatan pengeluaran operasional
            <span class="font-semibold text-brand-700">{{ $unit->nama }}</span>
            ke Jurnal Umum.
        </p>
    </div>

    {{-- Error global --}}
    @error('items')
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="submit" class="flex flex-col gap-5">

        {{-- Tanggal --}}
        <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-brand-100">
            <h2 class="text-base font-semibold text-brand-900 mb-4 flex items-center gap-2">
                <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                Tanggal Pengeluaran
            </h2>
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-zinc-700">Tanggal <span class="text-red-500">*</span></label>
                <div wire:ignore x-data="{ val: $wire.entangle('tanggal') }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            dateFormat: 'Y-m-d',
            defaultDate: val,
            altInput: true,
            altFormat: 'd F Y',
            disableMobile: true
        })"
        class="w-full rounded-xl border-2 border-brand-500 text-zinc-900 px-3.5 py-2.5 text-base shadow-sm focus:border-brand-600 focus:ring-0 focus:outline-none transition-colors @error('tanggal') border-red-400 @enderror cursor-pointer" />
</div>
                @error('tanggal')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Daftar Item Pengeluaran --}}
        <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-zinc-100 bg-zinc-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-base sm:text-lg font-semibold text-zinc-800 flex items-center gap-2">
                    <svg class="size-5 text-zinc-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-19.5 5.25h19.5m-19.5 0h19.5M2.25 15h19.5M2.25 15.75h19.5m-19.5-12h19.5A2.25 2.25 0 0 1 24 6v12a2.25 2.25 0 0 1-2.25 2.25H2.25A2.25 2.25 0 0 1 0 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                    </svg>
                    <span class="text-zinc-900 font-bold text-sm sm:text-base">Rincian Pengeluaran</span>
                </h2>
                <button type="button" wire:click="addItem"
                    class="flex items-center justify-center gap-1.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 px-3.5 py-2.5 rounded-xl transition-all active:scale-95 shadow-sm w-full sm:w-auto shrink-0">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Item
                </button>
            </div>

            <div class="p-4 sm:p-5 flex flex-col gap-4">
                @foreach ($items as $idx => $item)
                    <div class="bg-zinc-50/70 border border-zinc-200 rounded-xl p-4 flex flex-col gap-3.5 relative">
                        {{-- Header Item --}}
                        <div class="flex items-center justify-between border-b border-zinc-200/60 pb-2">
                            <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">
                                Item Pengeluaran #{{ $idx + 1 }}
                            </span>
                            @if (count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $idx }})"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg p-1.5 transition-colors active:scale-95"
                                    title="Hapus Item">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>

                        {{-- Grid: Jenis Biaya & Keterangan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-zinc-600">Jenis Biaya (Akun) <span class="text-red-500">*</span></label>
                                <select wire:model.live="items.{{ $idx }}.kode_akun_id"
                                    class="w-full rounded-xl border border-zinc-300 text-zinc-900 px-3 py-2 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-colors bg-white @error('items.'.$idx.'.kode_akun_id') border-red-400 @enderror">
                                    <option value="">-- Pilih Jenis Biaya --</option>
                                    @foreach ($akunBiaya as $akun)
                                        <option value="{{ $akun->id }}">{{ $akun->kode }} | {{ $akun->nama }}</option>
                                    @endforeach
                                </select>
                                @error('items.' . $idx . '.kode_akun_id')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-zinc-600">Keterangan / Kebutuhan <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.live.debounce.300ms="items.{{ $idx }}.keterangan"
                                    placeholder="cth: Beli sabun, bensin, dll..."
                                    class="w-full rounded-xl border border-zinc-300 text-zinc-900 px-3 py-2 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-colors bg-white @error('items.'.$idx.'.keterangan') border-red-400 @enderror">
                                @error('items.' . $idx . '.keterangan')
                                    <p class="text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Nominal --}}
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-zinc-600">Nominal Pengeluaran <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs text-zinc-400 font-medium pointer-events-none">Rp</span>
                                <input type="number" inputmode="numeric"
                                    wire:model.live.debounce.300ms="items.{{ $idx }}.nominal"
                                    placeholder="0" min="0"
                                    class="text-xs pl-8 w-full rounded-xl border border-zinc-300 px-3 py-2 text-zinc-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-colors bg-white @error('items.'.$idx.'.nominal') border-red-400 @enderror">
                            </div>
                            @error('items.' . $idx . '.nominal')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Subtotal Pengeluaran --}}
            @if ($totalPengeluaran > 0)
                <div class="px-5 py-3.5 bg-zinc-50 border-t border-zinc-200 flex justify-between items-center text-sm font-semibold">
                    <span class="text-zinc-600">Total Pengeluaran</span>
                    <span class="text-red-600 text-base">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        {{-- Info: koneksi ke pendapatan --}}
        <div class="bg-brand-50 border border-brand-200 rounded-2xl px-4 py-3 flex items-start gap-3">
            <svg class="size-4 text-brand-600 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <p class="text-xs text-brand-800 leading-relaxed">
                Pengeluaran yang dicatat di sini akan <strong>otomatis terhubung ke Jurnal Umum</strong>.
                Jika ada transaksi pemasukan di tanggal yang sama, total pengeluaran akan dikurangkan
                dari pendapatan bersih unit Anda.
            </p>
        </div>

        {{-- Spacer untuk sticky footer --}}
        <div class="h-24"></div>

        {{-- Footer Sticky / Tombol Submit --}}
        <div class="sticky bottom-4 z-10 bg-white rounded-2xl p-5 sm:p-6 shadow-md border border-brand-100 flex flex-col gap-4">
            <div class="flex justify-between items-center px-1">
                <div>
                    <span class="text-zinc-500 font-medium text-sm">Total Pengeluaran</span>
                    <span class="block text-xl sm:text-2xl font-extrabold text-red-600">
                        Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs text-zinc-400">Unit</span>
                    <span class="block text-sm font-semibold text-brand-700">{{ $unit->nama }}</span>
                </div>
            </div>
            <button type="submit"
                class="w-full min-h-[56px] rounded-2xl text-base font-semibold shadow-md border border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
                wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
                    </svg>
                    Simpan Pengeluaran
                </span>
                <span wire:loading wire:target="submit" class="flex items-center gap-2">
                    <svg class="animate-spin size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>

    </form>
</div>
