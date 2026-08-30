<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-20">
    @if (session()->has('status'))
        <div class="p-4 mb-2 text-sm text-brand-900 bg-brand-100 rounded-xl border border-brand-200 flex items-center gap-3 shadow-sm"
            role="alert">
            <svg class="size-5 text-brand-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">{{ session('status') }}</span>
        </div>
    @endif


        {{-- Edit Mode Banner --}}
        @if ($isEditing)
            <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm">
                <div class="size-9 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="size-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-amber-900">Mode Edit</p>
                    <p class="text-xs text-amber-700 mt-0.5">Anda sedang mengubah data transaksi yang sudah ada. Simpan untuk memperbarui.</p>
                </div>
                <flux:button variant="ghost" size="sm" :href="route('riwayat-rekap')" wire:navigate icon="x-mark" class="shrink-0 text-amber-700 hover:bg-amber-100" />
            </div>
        @endif

        <form wire:submit="submit" class="flex flex-col gap-5 sm:gap-6">

            {{-- Header / Tanggal --}}
            <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-brand-100">
                <h2 class="text-lg font-semibold text-brand-900 mb-4 flex items-center gap-2">
                    <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Periode Transaksi
                </h2>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Tanggal Input</label>
                    @if ($isMingguan)
                        <div class="p-4 bg-brand-50 border border-brand-200 rounded-xl text-brand-900 font-medium">
                            {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }} -
                            {{ \Carbon\Carbon::parse($tanggalAkhir)->translatedFormat('d F Y') }}
                        </div>
                        <div class="text-xs text-brand-600 mt-2 flex items-start gap-1.5">
                            <svg class="size-4 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            Otomatis menyesuaikan minggu berjalan (Senin-Minggu)
                        </div>
                    @else
                        <div wire:ignore x-data="{ val: $wire.entangle('tanggal').live }">
                            <input type="text" x-model="val"
                                x-init="window.flatpickr($el, {
                                    locale: window.flatpickrIndonesian,
                                    dateFormat: 'Y-m-d',
                                    maxDate: 'today',
                                    defaultDate: val,
                                    altInput: true,
                                    altFormat: 'd F Y',
                                    disableMobile: true
                                })"
                                class="text-base w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-zinc-900 focus:outline-none focus:border-brand-600 focus:ring-0 transition-colors shadow-sm cursor-pointer" />
                        </div>
                    @endif
                    @error('tanggal')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if ($sudahInput && !$isEditing)
                <div class="bg-white rounded-2xl p-8 sm:p-10 shadow-sm border border-brand-100 text-center flex flex-col items-center justify-center">
                    <div class="size-16 rounded-full bg-brand-100 flex items-center justify-center mb-4">
                        <svg class="size-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-brand-900 mb-2">Transaksi Sudah Diisi</h2>
                    <p class="text-zinc-500 mb-6 max-w-md">
                        Anda sudah mengisi transaksi untuk {{ $isMingguan ? 'minggu' : 'tanggal' }} ini
                        ({{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}).
                        Silakan cek halaman Riwayat & Rekap untuk melihat atau mengubah detailnya.
                    </p>
                    <flux:button variant="primary" :href="route('unit.riwayat-transaksi')" wire:navigate icon="document-chart-bar">
                        Lihat Riwayat & Rekap
                    </flux:button>
                </div>
            @else
                {{-- Daftar Kategori --}}
                <div class="bg-white rounded-2xl shadow-sm border border-brand-100 overflow-hidden">
                <div class="p-5 border-b border-brand-100 bg-brand-50/50">
                    <h2 class="text-lg font-semibold text-brand-900 flex items-center gap-2">
                        <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <text x="12" y="15.5" text-anchor="middle" font-weight="700" font-size="10" fill="currentColor" stroke="none" font-family="sans-serif">Rp</text>
                        </svg>
                        Rincian Pemasukan
                    </h2>
                </div>

                <div class="flex flex-col divide-y divide-brand-100">
                    @foreach ($inputs as $id => $input)
                        <div class="p-5 flex flex-col gap-3">
                            <div class="flex justify-between items-start mb-2">
                                <label
                                    class="text-base font-semibold text-zinc-900">{{ $this->kategoriList->get($id)->nama }}</label>
                                @if ($input['tipe'] === 'harga_x_qty' || $input['tipe'] === 'flat')
                                    <span
                                        class="text-xs font-semibold px-2.5 py-1 bg-brand-100 text-brand-800 rounded-md border border-brand-200">
                                        Rp
                                        {{ number_format($this->kategoriList->get($id)->hargaSaat(\Carbon\Carbon::parse($tanggal)), 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            @if ($input['tipe'] === 'harga_x_qty' || $input['tipe'] === 'tahunan')
                                <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                                    <div class="w-full sm:w-1/2">
                                        <input type="number" inputmode="numeric" placeholder="Jumlah"
                                            wire:model.live.debounce.300ms="inputs.{{ $id }}.qty"
                                            class="text-base w-full rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-zinc-900 focus:outline-none focus:border-brand-600 focus:ring-0 transition-colors shadow-sm"
                                            min="0" />
                                    </div>
                                    <div class="w-full sm:w-1/2 sm:text-right">
                                        <div class="text-xs text-zinc-500 mb-1 font-medium uppercase tracking-wider">
                                            Subtotal</div>
                                        <div class="text-lg font-semibold text-brand-700">
                                            Rp {{ number_format($input['subtotal'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            @elseif ($input['tipe'] === 'flat')
                                <div
                                    class="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between bg-zinc-50 p-3 rounded-xl border border-zinc-100">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model.live="inputs.{{ $id }}.aktif"
                                            class="size-4 rounded border-2 border-brand-500 text-brand-600 focus:ring-0">
                                        <span class="text-sm font-medium text-zinc-700">Ada Pemasukan</span>
                                    </label>
                                    <div class="sm:text-right">
                                        <div class="text-lg font-semibold text-brand-700">
                                            Rp {{ number_format($input['subtotal'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                            @elseif ($input['tipe'] === 'bebas')
                                <div class="flex flex-col sm:flex-row gap-4 sm:items-center">
                                    <div class="w-full relative">
                                        <span
                                            class="absolute inset-y-0 left-0 flex items-center pl-4 text-zinc-500 font-medium pointer-events-none">Rp</span>
                                        <input type="number" inputmode="numeric" placeholder="0"
                                            wire:model.live.debounce.300ms="inputs.{{ $id }}.nominal"
                                            class="text-base pl-10 w-full font-medium rounded-xl border-2 border-brand-500 px-3.5 py-2.5 text-zinc-900 focus:outline-none focus:border-brand-600 focus:ring-0 transition-colors shadow-sm [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0"
                                            min="0" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Error Summary --}}
            @if ($errors->has('submit') || $errors->has('totalPemasukan'))
                <div class="p-4 text-sm text-red-800 bg-red-100 rounded-xl border border-red-200 flex items-start gap-3 shadow-sm"
                    role="alert">
                    <svg class="size-5 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <span
                        class="font-medium leading-relaxed">{{ $errors->first('submit') ?? $errors->first('totalPemasukan') }}</span>
                </div>
            @endif

            {{-- Spacer untuk scroll di atas sticky footer (mobile friendly) --}}
            <div class="h-32 min-h-[8rem] shrink-0 w-full sm:hidden"></div>

            <div class="fixed bottom-0 left-0 right-0 z-20 sm:relative sm:bottom-auto sm:left-auto sm:right-auto sm:z-auto sm:mt-2">
                <div class="bg-white sm:rounded-2xl p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-[0_-8px_32px_-8px_rgba(0,0,0,0.1)] sm:shadow-md border-t sm:border border-brand-100 flex flex-col gap-4">
                    <div class="flex justify-between items-center px-1 sm:px-2 mb-1">
                        <span class="text-zinc-600 font-semibold text-sm sm:text-base">Total Pemasukan</span>
                        <span class="text-xl sm:text-2xl font-extrabold text-brand-600 tracking-tight whitespace-nowrap">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
                    </div>

                    {{-- Tombol submit tinggi minimum 52px agar touch friendly --}}
                    <button type="submit"
                        class="w-full min-h-[56px] rounded-2xl text-base font-semibold shadow-md border border-transparent {{ $isEditing ? 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-500/20' : 'bg-brand-600 hover:bg-brand-700 focus:ring-brand-500/20' }} text-white focus:outline-none focus:ring-4 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer">
                        @if ($isEditing)
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Perbarui Transaksi
                        @else
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 019 9v.375M10.125 2.25A3.375 3.375 0 0113.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 013.375 3.375M9 15l2.25 2.25L15 12" />
                            </svg>
                            Simpan Transaksi
                        @endif
                    </button>
                </div>
            </div>


        @endif
    </form>

    {{-- Modal Error Input Ganda --}}
    <flux:modal wire:model="showDuplicateError" class="min-w-[400px]">
        <div class="flex flex-col gap-6">
            <div class="flex flex-col items-center justify-center text-center gap-4">
                <div class="size-16 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="size-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <flux:heading size="lg">Input Ganda Ditolak</flux:heading>
                    <flux:subheading class="mt-2 text-zinc-500">
                        Data transaksi untuk periode/tanggal ini sudah pernah diinput.<br>
                        Sistem memblokir input ganda. Silakan edit data yang sudah ada di Riwayat & Rekap.
                    </flux:subheading>
                </div>
            </div>

            <div class="flex justify-center w-full">
                <flux:button variant="primary" wire:click="$set('showDuplicateError', false)" class="w-full sm:w-auto px-8">
                    Mengerti & Tutup
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
