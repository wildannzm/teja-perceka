<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-full mx-auto pb-10 min-w-0">

    <x-page-header title="Riwayat & Rekap" description="Rincian pendapatan operasional dan riwayat jurnal transaksi." />

    {{-- Filter section --}}
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
            {{-- Mode selector --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto">
                <label class="text-sm font-medium text-zinc-700">Periode</label>
                <select wire:model.live="mode"
                    class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    @if(!$this->isHarianDisabled)
                        <option value="harian">Harian</option>
                    @endif
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="semester">Semester</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>

            {{-- Date/period picker --}}
            <div class="flex flex-col gap-1.5 w-full md:w-auto md:min-w-48">
                <label class="text-sm font-medium text-zinc-700">Pilih {{ ucfirst($mode) }}</label>
                
                @if($mode === 'harian')
                    <div wire:ignore wire:key="picker-harian" x-data="{ val: $wire.entangle('tanggal').live }">
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
                    <div wire:ignore wire:key="picker-mingguan" x-data="{ val: $wire.entangle('minggu').live }">
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
                @elseif($mode === 'bulanan')
                    <div wire:ignore wire:key="picker-bulanan" x-data="{ val: $wire.entangle('bulan').live }">
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
                    <div wire:key="picker-semester" class="flex gap-2">
                        <select wire:model.live="semester"
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                            <option value="1">Sem 1 (Jan-Jun)</option>
                            <option value="2">Sem 2 (Jul-Des)</option>
                        </select>
                        <input type="number" wire:model.live="semesterTahun" min="2020" max="2099" placeholder="{{ date('Y') }}"
                            class="w-24 shrink-0 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    </div>

                @elseif($mode === 'tahunan')
                    <input wire:key="picker-tahunan" type="number" wire:model.live="tahun" min="2020" placeholder="{{ date('Y') }}"
                        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                @endif
            </div>

            @if($tab === 'jurnal')
                <div class="flex flex-col gap-1.5 w-full md:w-auto">
                    <label class="text-sm font-medium text-zinc-700">Urutkan</label>
                    <select wire:model.live="sortOption"
                        class="w-full sm:min-w-48 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                        <option value="tanggal-asc">Tanggal · Terlama</option>
                        <option value="tanggal-desc">Tanggal · Terbaru</option>
                        <option value="nomor_bukti-asc">Bukti · A–Z</option>
                        <option value="nomor_bukti-desc">Bukti · Z–A</option>
                    </select>
                </div>
            @endif
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

    {{-- Tab content --}}
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
                :sort-field="$sortField"
                :sort-direction="$sortDirection"
                wire:key="tab-jurnal-{{ $unit_id }}-{{ $mode }}-{{ $tanggal }}-{{ $minggu }}-{{ $bulan }}-{{ $semester }}-{{ $semesterTahun }}-{{ $tahun }}-{{ $sortField }}-{{ $sortDirection }}"
            />
        @endif
    </div>

</div>
