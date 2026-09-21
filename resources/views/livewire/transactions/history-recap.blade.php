<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-full mx-auto pb-10 min-w-0">

    <x-page-header title="Riwayat & Rekap" description="Rincian pendapatan operasional dan history jurnal transaksi." />

    {{-- Filter section --}}
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-zinc-200 shadow-sm flex flex-col gap-5">


        <div class="flex flex-col md:flex-row gap-5 md:items-end flex-wrap">
            {{-- Entity / Unit selector (for non-unit-heads only) --}}
            @unless(auth()->user()->hasRole('kepala_unit'))
                <div class="flex flex-col gap-1.5 w-full md:w-auto md:min-w-48">
                    <label class="text-sm font-medium text-zinc-700">Entitas / Unit Usaha</label>
                    <select wire:model.live="unit_id"
                        class="w-full sm:min-w-48 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors font-medium">
                        <option value="bumdes">BUMDes</option>
                        <option value="all">Semua Unit Usaha</option>
                        <optgroup label="Per Unit Usaha">
                            @foreach($this->units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            @endunless

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
                    <div wire:ignore wire:key="picker-harian" x-data="{ val: $wire.entangle('transactionDate').live }">
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
                    <div wire:ignore wire:key="picker-mingguan" x-data="{ val: $wire.entangle('week').live }">
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
                @elseif($mode === 'monthly')
                    <div wire:ignore wire:key="picker-bulanan" x-data="{ val: $wire.entangle('month').live }">
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
                        <input type="number" wire:model.live.debounce.500ms="semesterYear" min="2020" max="2099" placeholder="{{ date('Y') }}"
                            class="w-24 shrink-0 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                    </div>

                @elseif($mode === 'yearly')
                    <input wire:key="picker-tahunan" type="number" wire:model.live.debounce.500ms="year" min="2020" placeholder="{{ date('Y') }}"
                        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                @endif
            </div>

            @if($tab === 'journal')
                <div class="flex flex-col gap-1.5 w-full md:w-auto">
                    <label class="text-sm font-medium text-zinc-700">Urutkan</label>
                    <select wire:model.live="sortOption"
                        class="w-full sm:min-w-48 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                        <option value="transaction_date-asc">Tanggal · Terlama</option>
                        <option value="transaction_date-desc">Tanggal · Terbaru</option>
                        <option value="voucher_number-asc">Bukti · A–Z</option>
                        <option value="voucher_number-desc">Bukti · Z–A</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5 w-full md:w-auto">
                    <label class="text-sm font-medium text-zinc-700">Tampilan</label>
                    <select wire:model.live="viewMode"
                        class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                        <option value="summary">Ringkas</option>
                        <option value="detailed">Rinci</option>
                    </select>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200 flex space-x-6 px-2 overflow-x-auto">
        <button wire:click="$set('tab', 'revenue')" 
                class="pb-3 px-1 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap focus:outline-none {{ $tab === 'revenue' ? 'border-brand-500 text-brand-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
            Pendapatan
        </button>
        <button wire:click="$set('tab', 'journal')" 
                class="pb-3 px-1 text-sm font-semibold transition-colors border-b-2 whitespace-nowrap focus:outline-none {{ $tab === 'journal' ? 'border-brand-500 text-brand-700' : 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300' }}">
            Jurnal Umum
        </button>
    </div>

    {{-- Tab content --}}
    <div class="mt-2 min-w-0">
        @if($tab === 'revenue')
            <livewire:transactions.revenue-tab 
                :unit-id="$unit_id"
                :mode="$mode"
                :transactionDate="$transactionDate"
                :week="$week"
                :month="$month"
                :semester="$semester"
                :semester-tahun="$semesterYear"
                :year="$year" 
                wire:key="tab-pendapatan-{{ $unit_id }}-{{ $mode }}-{{ $transactionDate }}-{{ $week }}-{{ $month }}-{{ $semester }}-{{ $semesterYear }}-{{ $year }}"
            />
        @elseif($tab === 'journal')
            <livewire:transactions.journal-tab
                :unit-id="$unit_id"
                :mode="$mode"
                :transactionDate="$transactionDate"
                :week="$week"
                :month="$month"
                :semester="$semester"
                :semester-tahun="$semesterYear"
                :year="$year"
                :sort-field="$sortField"
                :sort-direction="$sortDirection"
                :viewMode="$viewMode"
                wire:key="tab-jurnal-{{ $unit_id }}-{{ $mode }}-{{ $transactionDate }}-{{ $week }}-{{ $month }}-{{ $semester }}-{{ $semesterYear }}-{{ $year }}-{{ $sortField }}-{{ $sortDirection }}-{{ $viewMode }}"
            />
        @endif
    </div>

</div>
