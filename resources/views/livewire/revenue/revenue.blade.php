<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-full mx-auto pb-10 min-w-0">

    <x-page-header title="Pendapatan" description="Rincian pendapatan operasional berdasarkan kategori transaksi." />

    {{-- Filter section --}}
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-zinc-200 shadow-sm flex flex-col gap-5">
        
        @unless($this->isKepalaUnit)
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                <select wire:model.live="unit_id"
                    class="w-full sm:max-w-xs rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    <option value="">Semua Unit</option>
                    @foreach($this->units as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="flex flex-col md:flex-row gap-5 md:items-end">
            {{-- Mode selector --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto">
                <label class="text-sm font-medium text-zinc-700">Periode</label>
                <select wire:model.live="mode"
                    class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    @if(!$this->isDailyDisabled)
                        <option value="daily">Harian</option>
                    @endif
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="semester">Semester</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>

            {{-- Date/period picker --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto md:min-w-48">
                <label class="text-sm font-medium text-zinc-700">Pilih {{ ucfirst($mode) }}</label>
                
                @if($mode === 'daily')
                    <div wire:ignore x-data="{ val: $wire.entangle('transactionDate').live }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            dateFormat: 'Y-m-d',
            defaultDate: val,
            altInput: true,
            altFormat: 'd F Y',
            disableMobile: true
        })"
        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer" />
</div>
                
                @elseif($mode === 'weekly')
                    @php $isWeekly = $this->selectedUnit && $this->selectedUnit->input_frequency === 'weekly'; @endphp
                    @if($isWeekly)
                        <select wire:model.live="week" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                            @forelse($this->availableWeeks as $week)
                                <option value="{{ $week->transaction_date->format('Y-m-d') }}">{{ $week->transaction_date->translatedFormat('d M') }} - {{ $week->end_date->translatedFormat('d M Y') }}</option>
                            @empty
                                <option value="">Belum ada input</option>
                            @endforelse
                        </select>
                    @else
                        <div wire:ignore x-data="{ val: $wire.entangle('week').live }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            dateFormat: 'Y-m-d',
            defaultDate: val,
            altInput: true,
            altFormat: 'd F Y',
            disableMobile: true
        })"
        title="Pilih tanggal dalam minggu yang dituju" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer" />
</div>
                    @endif
                
                @elseif($mode === 'monthly')
                    <div wire:ignore x-data="{ val: $wire.entangle('month').live }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            plugins: [
                new window.flatpickrMonthSelect({
                    shorthand: false,
                    dateFormat: 'Y-m',
                    altFormat: 'F Y',
                    theme: 'light'
                })
            ],
            defaultDate: val,
            altInput: true,
            disableMobile: true
        })"
        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer" />
</div>
                
                @elseif($mode === 'semester')
                    <div class="flex gap-2">
                        <select wire:model.live="semester"
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                            <option value="1">Sem 1 (Jan-Jun)</option>
                            <option value="2">Sem 2 (Jul-Des)</option>
                        </select>
                        <input type="number" wire:model.live.debounce.500ms="semesterYear" min="2020" max="2099" placeholder="{{ date('Y') }}"
                            class="w-24 shrink-0 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    </div>

                @elseif($mode === 'yearly')
                    <input type="number" wire:model.live.debounce.500ms="year" min="2020" placeholder="{{ date('Y') }}"
                        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                @endif
            </div>
        </div>
    </div>

    @php
        $data = $this->reportData;
        $isKonsolidasi = is_null($this->unit_id);
    @endphp

    {{-- Total income card (large summary) --}}
    <div class="bg-brand-500 text-white rounded-2xl p-6 sm:p-8 shadow-lg shadow-brand-500/20 border border-brand-400 flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
        <div class="min-w-0 w-full">
            <p class="text-brand-100 font-medium text-sm sm:text-base uppercase tracking-wide mb-1 break-words">{{ $data['unit'] }}</p>
            <p class="text-3xl sm:text-4xl font-bold tracking-tight break-words">Rp {{ number_format($data['totalRevenue'], 0, ',', '.') }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="text-brand-50 text-xs sm:text-sm font-medium bg-brand-600/50 px-3 py-1 rounded-full max-w-full truncate">
                    Periode: {{ $this->periodLabel }}
                </span>
            </div>
        </div>
        <div class="hidden sm:block opacity-20 shrink-0">
            <flux:icon.banknotes class="w-24 h-24" />
        </div>
    </div>

    {{-- Category breakdown --}}
    @if($data['isEmpty'])
        <div class="bg-white rounded-2xl border-2 border-dashed border-zinc-200 p-8 sm:p-12 text-center mt-2 flex flex-col items-center justify-center min-h-[300px] min-w-0">
            <div class="bg-zinc-100 text-zinc-400 p-4 rounded-full mb-4 inline-block">
                <flux:icon.document-magnifying-glass class="w-8 h-8" />
            </div>
            <h3 class="text-lg font-bold text-zinc-900 mb-2">Belum Ada Transaksi</h3>
            <p class="text-zinc-500 text-sm max-w-md mx-auto mb-6">{{ $data['emptyMessage'] }}</p>
            @if($this->isKepalaUnit)
                <flux:button variant="primary" icon="plus" href="{{ route('unit.input-transaksi') }}">
                    Input Transaksi Baru
                </flux:button>
            @endif
        </div>
    @else
        <div class="mt-2 min-w-0">
            @php
                // Group by unit if consolidation mode
                $groupedRows = collect($data['categoryRows'])->groupBy('unitName');
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
                                    <p class="font-bold text-zinc-900 text-base break-words">{{ $row['category'] }}</p>
                                    @if($row['type'] === 'harga_x_qty')
                                        <div class="flex flex-wrap items-center gap-1.5 text-sm text-zinc-500 font-medium min-w-0">
                                            <span class="bg-zinc-100 text-zinc-600 px-2 py-0.5 rounded-md border border-zinc-200 shrink-0">{{ number_format($row['totalQuantity'], 0, ',', '.') }}</span>
                                            <span class="shrink-0">&times;</span>
                                            <span class="shrink-0">Rp {{ number_format($row['unit_price'], 0, ',', '.') }}</span>
                                        </div>
                                    @else
                                        <div class="inline-flex">
                                            <span class="bg-zinc-100 text-zinc-500 px-2.5 py-0.5 rounded-md border border-zinc-200 text-xs font-semibold uppercase tracking-wider truncate">
                                                {{ $row['type'] === 'flat' ? 'Tarif Flat' : 'Manual / Bebas' }}
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
