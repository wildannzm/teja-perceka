<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-4xl mx-auto pb-10 min-w-0">

    {{-- Header --}}
    <div class="flex flex-col gap-1">
        <h1 class="text-xl sm:text-2xl font-semibold text-zinc-900">Riwayat & Rekap</h1>
        <p class="text-sm text-zinc-500">Rincian pendapatan operasional dan riwayat jurnal transaksi.</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-zinc-200 shadow-sm flex flex-col gap-5">
        
        @unless(auth()->user()->hasRole('kepala_unit'))
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                <select wire:model.live="unit_id"
                    class="w-full sm:max-w-xs rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    <option value="">Semua Unit</option>
                    @foreach($this->units as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="flex flex-col md:flex-row gap-5 md:items-end">
            {{-- Segmented Control --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto">
                <label class="text-sm font-medium text-zinc-700 hidden md:block">Periode</label>
                <div class="flex flex-wrap sm:flex-nowrap gap-1.5 p-1 bg-zinc-100 rounded-xl w-full border border-zinc-200/60">
                    @if(!$this->isHarianDisabled)
                        <button wire:click="$set('mode', 'harian')" class="{{ $mode === 'harian' ? 'bg-white shadow-sm text-zinc-900 font-semibold' : 'text-zinc-500 hover:text-zinc-700' }} flex-1 rounded-lg py-2 px-2 text-xs sm:text-sm font-medium transition-all">Harian</button>
                    @endif
                    <button wire:click="$set('mode', 'mingguan')" class="{{ $mode === 'mingguan' ? 'bg-white shadow-sm text-zinc-900 font-semibold' : 'text-zinc-500 hover:text-zinc-700' }} flex-1 rounded-lg py-2 px-2 text-xs sm:text-sm font-medium transition-all">Mingguan</button>
                    <button wire:click="$set('mode', 'bulanan')" class="{{ $mode === 'bulanan' ? 'bg-white shadow-sm text-zinc-900 font-semibold' : 'text-zinc-500 hover:text-zinc-700' }} flex-1 rounded-lg py-2 px-2 text-xs sm:text-sm font-medium transition-all">Bulanan</button>
                    <button wire:click="$set('mode', 'semester')" class="{{ $mode === 'semester' ? 'bg-white shadow-sm text-zinc-900 font-semibold' : 'text-zinc-500 hover:text-zinc-700' }} flex-1 rounded-lg py-2 px-2 text-xs sm:text-sm font-medium transition-all">Semester</button>
                    <button wire:click="$set('mode', 'tahunan')" class="{{ $mode === 'tahunan' ? 'bg-white shadow-sm text-zinc-900 font-semibold' : 'text-zinc-500 hover:text-zinc-700' }} flex-1 rounded-lg py-2 px-2 text-xs sm:text-sm font-medium transition-all">Tahunan</button>
                </div>
            </div>

            {{-- Date/Period Picker --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto md:min-w-48">
                <label class="text-sm font-medium text-zinc-700">Pilih {{ ucfirst($mode) }}</label>
                
                @if($mode === 'harian')
                    <div wire:ignore x-data="{ val: $wire.entangle('tanggal').live }">
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
                
                @elseif($mode === 'mingguan')
                    @php $isTps = $this->selectedUnit && $this->selectedUnit->frekuensi_input === 'mingguan'; @endphp
                    @if($isTps)
                        @php
                            // Load TPS weeks just for the filter if it's TPS
                            $availableTpsWeeks = \App\Models\TransaksiHarian::where('unit_wisata_id', $unit_id)->select('tanggal', 'tanggal_akhir')->distinct()->orderBy('tanggal', 'desc')->get();
                        @endphp
                        <select wire:model.live="minggu" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                            @forelse($availableTpsWeeks as $week)
                                <option value="{{ $week->tanggal->format('Y-m-d') }}">{{ $week->tanggal->translatedFormat('d M') }} - {{ $week->tanggal_akhir->translatedFormat('d M Y') }}</option>
                            @empty
                                <option value="">Belum ada input</option>
                            @endforelse
                        </select>
                    @else
                        <div wire:ignore x-data="{ val: $wire.entangle('minggu').live }">
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
                        <p class="text-[11px] text-zinc-400 mt-0.5 ml-1">Pilih hari apa saja dalam 1 minggu</p>
                    @endif
                
                @elseif($mode === 'bulanan')
                    <div wire:ignore x-data="{ val: $wire.entangle('bulan').live }">
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
                        <input type="number" wire:model.live="semesterTahun" min="2020" max="2099" placeholder="{{ date('Y') }}"
                            class="w-24 shrink-0 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    </div>

                @elseif($mode === 'tahunan')
                    <input type="number" wire:model.live="tahun" min="2020" placeholder="{{ date('Y') }}"
                        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200 flex space-x-6 px-2 overflow-x-auto">
        <button wire:click="$set('tab', 'pendapatan')" 
                class="pb-3 px-1 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap focus:outline-none {{ $tab === 'pendapatan' ? 'border-brand-500 text-brand-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
            Pendapatan
        </button>
        <button wire:click="$set('tab', 'jurnal')" 
                class="pb-3 px-1 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap focus:outline-none {{ $tab === 'jurnal' ? 'border-brand-500 text-brand-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
            Jurnal Umum
        </button>
    </div>

    {{-- Tab Content --}}
    <div class="mt-2 min-w-0">
        @if($tab === 'pendapatan')
            <livewire:transaksi.tab-pendapatan 
                :unit-id="$unit_id"
                :mode="$mode"
                :tanggal="$tanggal"
                :minggu="$minggu"
                :bulan="$bulan"
                :semester="$semester"
                :semester-tahun="$semesterTahun"
                :tahun="$tahun" 
                wire:key="tab-pendapatan-{{ $unit_id }}-{{ $mode }}-{{ $tanggal }}-{{ $minggu }}-{{ $bulan }}-{{ $semester }}-{{ $semesterTahun }}-{{ $tahun }}"
            />
        @elseif($tab === 'jurnal')
            <livewire:transaksi.tab-jurnal
                :unit-id="$unit_id"
                :mode="$mode"
                :tanggal="$tanggal"
                :minggu="$minggu"
                :bulan="$bulan"
                :semester="$semester"
                :semester-tahun="$semesterTahun"
                :tahun="$tahun" 
                wire:key="tab-jurnal-{{ $unit_id }}-{{ $mode }}-{{ $tanggal }}-{{ $minggu }}-{{ $bulan }}-{{ $semester }}-{{ $semesterTahun }}-{{ $tahun }}"
            />
        @endif
    </div>

</div>
