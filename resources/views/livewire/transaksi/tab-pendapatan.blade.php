{{-- Tab Pendapatan --}}
<div class="flex flex-col gap-4 sm:gap-6 min-w-0">

    @php
        $data = $this->reportData;
        $isKonsolidasi = is_null($unitId);
    @endphp

    {{-- Total Pendapatan Card --}}
    <div class="bg-brand-500 text-white rounded-2xl p-6 sm:p-8 shadow-lg shadow-brand-500/20 border border-brand-400 flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
        <div class="min-w-0 w-full">
            <p class="text-brand-100 font-medium text-sm sm:text-base uppercase tracking-wide mb-1 break-words">{{ $data['unit'] }}</p>
            
            @if($data['totalPengeluaranUnit'] > 0)
                <p class="text-xs text-brand-200 uppercase font-medium tracking-wide">Pemasukan Bersih (Net)</p>
                <p class="text-3xl sm:text-4xl font-bold tracking-tight break-words">Rp {{ number_format($data['pendapatanBersih'], 0, ',', '.') }}</p>
                
                <div class="mt-4 pt-4 border-t border-brand-400/60 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-brand-200 uppercase font-medium tracking-wide">Pemasukan Kotor</p>
                        <p class="text-lg sm:text-xl font-bold">Rp {{ number_format($data['totalPendapatan'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-brand-200 uppercase font-medium tracking-wide">Pengeluaran Unit</p>
                        <p class="text-lg sm:text-xl font-bold text-red-200">- Rp {{ number_format($data['totalPengeluaranUnit'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @else
                <p class="text-3xl sm:text-4xl font-bold tracking-tight break-words">Rp {{ number_format($data['totalPendapatan'], 0, ',', '.') }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                <span class="text-brand-50 text-xs sm:text-sm font-medium bg-brand-600/50 px-3 py-1 rounded-full max-w-full truncate">
                    Periode: {{ $this->periodeLabel }}
                </span>
            </div>
        </div>
        <div class="hidden sm:block opacity-20 shrink-0">
            <flux:icon.banknotes class="w-24 h-24" />
        </div>
    </div>

    {{-- Breakdown Kategori --}}
    @if($data['kosong'])
        <div class="bg-white rounded-2xl border-2 border-dashed border-zinc-200 p-8 sm:p-12 text-center mt-2 flex flex-col items-center justify-center min-h-[300px] min-w-0">
            <div class="bg-zinc-100 text-zinc-400 p-4 rounded-full mb-4 inline-block">
                <flux:icon.document-magnifying-glass class="w-8 h-8" />
            </div>
            <h3 class="text-lg font-bold text-zinc-900 mb-2">Belum Ada Transaksi</h3>
            <p class="text-zinc-500 text-sm max-w-md mx-auto mb-6">{{ $data['pesanKosong'] }}</p>
            @if($this->isKepalaUnit)
                <flux:button variant="primary" icon="plus" href="{{ route('unit.input-transaksi') }}">
                    Input Transaksi Baru
                </flux:button>
            @endif
        </div>
    @else
        <div class="mt-2 min-w-0">
            @php
                $groupedRows = collect($data['kategoriRows'])->groupBy('unit_nama');
            @endphp

            @foreach($groupedRows as $unitName => $rows)
                <div class="mb-8 min-w-0">
                    @if($isKonsolidasi)
                        <div class="flex items-center gap-2 mb-4 px-1 min-w-0">
                            <flux:icon.building-storefront class="w-5 h-5 text-zinc-400 shrink-0" />
                            <h3 class="text-base font-bold text-zinc-800 uppercase tracking-wide truncate">{{ $unitName }}</h3>
                            <div class="flex-grow h-px bg-zinc-200 ml-2"></div>
                        </div>
                    @else
                        <h3 class="text-base font-bold text-zinc-800 uppercase tracking-wide mb-4 px-1">Rincian Kategori</h3>
                    @endif

                    <div class="grid grid-cols-1 gap-3 sm:gap-4 min-w-0">
                        @foreach($rows as $row)
                            <div class="bg-white rounded-xl border border-zinc-200 p-4 sm:p-5 shadow-sm flex flex-row items-center justify-between hover:border-brand-300 hover:shadow-md transition-all gap-4 min-w-0">
                                <div class="flex flex-col gap-1 flex-1 min-w-0">
                                    <p class="font-bold text-zinc-900 text-base break-words">{{ $row['kategori'] }}</p>
                                    @if($row['tipe'] === 'harga_x_qty')
                                        <div class="flex flex-wrap items-center gap-1.5 text-sm text-zinc-500 font-medium min-w-0">
                                            <span class="bg-zinc-100 text-zinc-600 px-2 py-0.5 rounded-md border border-zinc-200 shrink-0">{{ number_format($row['jumlah_qty'], 0, ',', '.') }}</span>
                                            <span class="shrink-0">&times;</span>
                                            <span class="shrink-0">Rp {{ number_format($row['harga_satuan'], 0, ',', '.') }}</span>
                                        </div>
                                    @else
                                        <div class="inline-flex">
                                            <span class="bg-zinc-100 text-zinc-500 px-2.5 py-0.5 rounded-md border border-zinc-200 text-xs font-semibold uppercase tracking-wider truncate">
                                                {{ $row['tipe'] === 'flat' ? 'Tarif Flat' : 'Manual / Bebas' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-extrabold text-brand-700 text-lg sm:text-xl tracking-tight">Rp {{ number_format($row['subtotal'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
