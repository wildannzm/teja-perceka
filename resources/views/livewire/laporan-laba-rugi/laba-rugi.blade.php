<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-5xl mx-auto pb-10">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl sm:text-2xl font-semibold text-zinc-900">Laporan Laba Rugi</h1>
            <p class="text-sm text-zinc-500">Rekap pendapatan dan biaya operasional per periode.</p>
        </div>
        @if($this->canPrint)
            <flux:button variant="primary" icon="document-arrow-down"
                wire:click="exportPdf" wire:loading.attr="disabled"
                class="w-full sm:w-auto shrink-0">
                Cetak PDF
            </flux:button>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white p-4 sm:p-6 rounded-xl border border-brand-100 shadow-sm">
        <h2 class="text-base sm:text-lg font-semibold mb-4 text-zinc-900">Filter Laporan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $this->isKepalaUnit ? '' : 'md:grid-cols-3' }} gap-3 sm:gap-4">

            {{-- Unit selector (non kepala_unit only) --}}
            @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                    <select wire:model.live="unit_id"
                        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">Semua Unit (Konsolidasi)</option>
                        @foreach($this->units as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            {{-- Mode --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-zinc-700">Periode</label>
                <select wire:model.live="mode"
                    class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>

            {{-- Input periode --}}
            <div class="flex flex-col gap-1.5">
                @if($mode === 'bulanan')
                    <label class="text-sm font-medium text-zinc-700">Bulan</label>
                    <input type="month" wire:model.live="periode"
                        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                @else
                    <label class="text-sm font-medium text-zinc-700">Tahun</label>
                    <input type="number" wire:model.live="periode" min="2020" max="2099"
                        placeholder="{{ date('Y') }}"
                        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                @endif
            </div>
        </div>
    </div>

    {{-- Laporan --}}
    @php
        $data = $this->reportData;
        $pendapatanRows = $data['pendapatanRows'];
        $totalPendapatan = $data['totalPendapatan'];
        $hppRows = $data['hppRows'];
        $totalHpp = $data['totalHpp'];
        $labaKotor = $data['labaKotor'];
        $bebanRows = $data['bebanRows'];
        $totalBeban = $data['totalBeban'];
        $pendapatanLainRows = $data['pendapatanLainRows'];
        $totalPendapatanLain = $data['totalPendapatanLain'];
        $bebanLainRows = $data['bebanLainRows'];
        $totalBebanLain = $data['totalBebanLain'];
        $labaBersih = $data['labaBersih'];
    @endphp

    <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">

        {{-- Sub-header laporan --}}
        <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
            <p class="text-sm font-bold text-zinc-900 uppercase">{{ $namaEntitas }}</p>
            <p class="text-sm font-bold text-zinc-900">LABA RUGI</p>
            <p class="text-sm text-zinc-600">{{ $tanggalCetak }}</p>
        </div>

        <div class="overflow-hidden md:overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-700 whitespace-normal md:whitespace-nowrap block md:table">
                <thead class="hidden md:table-header-group text-xs font-bold text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 w-32">KODE AKUN</th>
                        <th class="px-4 sm:px-6 py-3">NAMA AKUN</th>
                        <th class="px-4 sm:px-6 py-3 text-right w-40">JUMLAH</th>
                        <th class="px-4 sm:px-6 py-3 text-right w-40">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="block md:table-row-group">

                    {{-- ── PENDAPATAN USAHA ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            PENDAPATAN USAHA
                        </td>
                    </tr>
                    @foreach($pendapatanRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->kode }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->nama }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                {{ $row->jumlah != 0 ? 'Rp '.number_format($row->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH PENDAPATAN
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH PENDAPATAN
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalPendapatan != 0 ? 'Rp '.number_format($totalPendapatan, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── HARGA POKOK PENJUALAN ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            HARGA POKOK PENJUALAN
                        </td>
                    </tr>
                    @foreach($hppRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->kode }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->nama }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                {{ $row->jumlah != 0 ? 'Rp '.number_format($row->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH HPP
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH HPP
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalHpp != 0 ? 'Rp '.number_format($totalHpp, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <tr class="flex justify-between items-center md:table-row bg-brand-50/50 border-t border-brand-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-brand-900 uppercase text-xs tracking-wide text-right">
                            LABA KOTOR
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-brand-900 uppercase text-xs tracking-wide">
                            LABA KOTOR
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-brand-900">
                            {{ $labaKotor != 0 ? 'Rp '.number_format($labaKotor, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── BIAYA USAHA ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            BIAYA USAHA
                        </td>
                    </tr>
                    @foreach($bebanRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->kode }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->nama }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-red-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                {{ $row->jumlah != 0 ? 'Rp '.number_format($row->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH BIAYA USAHA
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH BIAYA USAHA
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalBeban != 0 ? 'Rp '.number_format($totalBeban, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── PENDAPATAN & BIAYA LAIN-LAIN ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            PENDAPATAN & BIAYA LAIN-LAIN
                        </td>
                    </tr>
                    @foreach($pendapatanLainRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->kode }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->nama }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                {{ $row->jumlah != 0 ? 'Rp '.number_format($row->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    @foreach($bebanLainRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->kode }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->nama }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-red-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                {{ $row->jumlah != 0 ? 'Rp '.number_format($row->jumlah, 0, ',', '.') : '-' }}
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach

                    {{-- ── LABA BERSIH ── --}}
                    <tr class="flex justify-between items-center md:table-row {{ $labaBersih >= 0 ? 'bg-emerald-600' : 'bg-red-600' }} px-4 py-4 md:p-0 mt-4 md:mt-0 rounded-b-xl md:rounded-none">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-4 font-extrabold text-white uppercase tracking-wide text-sm md:text-base text-right">
                            LABA BERSIH
                        </td>
                        <td class="block md:hidden md:px-6 md:py-4 font-extrabold text-white uppercase tracking-wide text-sm md:text-base">
                            LABA BERSIH
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-4 text-right font-extrabold text-white text-lg md:text-base">
                            {{ $labaBersih != 0 ? 'Rp '.number_format($labaBersih, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        {{-- Footer --}}
        <div class="mt-8 pt-8 px-4 sm:px-6 flex justify-end">
            <div class="text-center">
                <p class="text-sm text-zinc-700 mb-16">TEJA, {{ $tanggalTtd }}</p>
                <p class="text-sm font-bold text-zinc-900 uppercase underline underline-offset-4">{{ $penandatangan }}</p>
                <p class="text-sm text-zinc-600 mt-1">{{ $jabatan }}</p>
            </div>
        </div>
    </div>
</div>
