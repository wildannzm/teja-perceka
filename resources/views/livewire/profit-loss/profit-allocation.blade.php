<div class="flex flex-col gap-4 sm:gap-6 max-w-full mx-auto w-full pb-16 min-w-0">

    <x-page-header title="Alokasi Laba" description="Hitung alokasi dari Laba Bersih berdasarkan persentase.">
        <x-slot:actions>
            <button type="button" wire:click="exportPdf" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-4 py-2 bg-brand-600 text-white hover:bg-brand-700 font-semibold text-sm rounded-xl transition-colors shadow-sm">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Cetak PDF
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter panel --}}
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-zinc-200 shadow-sm flex flex-col md:flex-row gap-4 sm:gap-5 md:items-end flex-wrap">
        
        <div class="flex flex-col gap-1.5 w-full md:w-auto min-w-[160px]">
            <label class="text-sm font-medium text-zinc-700">Mode Periode</label>
            <select wire:model.live.debounce.250ms="mode"
                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                <option value="monthly">Bulanan</option>
                <option value="semester">Semester</option>
                <option value="yearly">Tahunan</option>
            </select>
        </div>

        <div class="flex flex-col gap-1.5 w-full md:w-auto min-w-[200px]">
            <label class="text-sm font-medium text-zinc-700">
                @if($mode === 'monthly') Pilih Bulan
                @elseif($mode === 'semester') Pilih Semester
                @else Pilih Tahun @endif
            </label>
            @if ($mode === 'monthly')
                <div wire:ignore wire:key="picker-bulan-{{ $mode }}" x-data="{ val: $wire.entangle('period').live }">
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
            @elseif ($mode === 'semester')
                <div wire:key="picker-semester-{{ $mode }}" class="flex gap-2">
                    <select wire:model.live.debounce.250ms="semester"
                        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                        <option value="1">Semester 1 (Jan - Jun)</option>
                        <option value="2">Semester 2 (Jul - Des)</option>
                    </select>
                    <input type="number" wire:model.live.debounce.500ms="semesterYear" min="2020" max="2099" placeholder="Tahun"
                        class="w-28 shrink-0 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                </div>
            @else
                <input wire:key="picker-tahun-{{ $mode }}" type="number" wire:model.live.debounce.500ms="period" min="2020" max="2099" placeholder="Pilih Tahun"
                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
            @endif
        </div>
    </div>
    {{-- Net profit info card --}}
    <div class="bg-brand-500 text-white rounded-2xl p-6 sm:p-8 shadow-lg shadow-brand-500/20 border border-brand-400 flex flex-col sm:flex-row sm:items-center justify-between gap-4 min-w-0">
        <div class="min-w-0 w-full">
            <p class="text-brand-100 font-medium text-sm sm:text-base uppercase tracking-wide mb-1 break-words">
                BUMDesa Teja Perceka
            </p>
            <p class="text-xs text-brand-200 uppercase font-medium tracking-wide">Total Laba Bersih</p>
            <p class="text-3xl sm:text-4xl font-bold tracking-tight break-words">
                Rp {{ number_format($this->netIncome, 0, ',', '.') }}
            </p>
            <div class="mt-3 flex flex-wrap gap-2">
                <span class="text-brand-50 text-xs sm:text-sm font-medium bg-brand-600/50 px-3 py-1 rounded-full max-w-full truncate">
                    Periode: {{ $mode === 'monthly' ? \Carbon\Carbon::parse($period)->translatedFormat('F Y') : ($mode === 'semester' ? 'Semester ' . $semester . ' ' . $semesterYear : 'Tahun ' . $period) }}
                </span>
            </div>
        </div>
        <div class="hidden sm:block opacity-20 shrink-0">
            <flux:icon.banknotes class="w-24 h-24" />
        </div>
    </div>

    {{-- Allocation table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-brand-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-100 flex items-center justify-between bg-zinc-50/50">
            <div>
                <h3 class="font-bold text-zinc-900">Rincian Alokasi</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Daftar pembagian persentase dari laba bersih</p>
            </div>
            
            @if($this->canEdit)
                <button type="button" wire:click="openCreate"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-brand-50 border border-brand-200 text-brand-700 rounded-xl text-xs font-bold hover:bg-brand-100 transition-colors shadow-sm active:scale-95">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Alokasi
                </button>
            @endif
        </div>


        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-semibold">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Keterangan</th>
                        <th class="px-4 sm:px-5 py-3 text-center">%</th>
                        <th class="px-4 sm:px-5 py-3 text-left">Nominal</th>
                        @if($this->canEdit)
                            <th class="px-4 sm:px-5 py-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    {{-- ── Initial NET PROFIT ─────────────────────────────────── --}}
                    <tr class="bg-brand-50 border-b border-brand-100">
                        <td class="px-4 sm:px-5 py-3.5 font-bold text-brand-900" colspan="2">LABA BERSIH</td>
                        <td class="px-4 sm:px-5 py-3.5 text-left font-sans font-bold text-brand-900">
                            Rp {{ number_format($this->netIncome, 0, ',', '.') }}
                        </td>
                        @if($this->canEdit)<td></td>@endif
                    </tr>

                    {{-- ── Deduction section ────────────────────────────────── --}}
                    @forelse ($this->deductionRows as $row)
                        <tr class="hover:bg-zinc-50 transition-colors border-b border-zinc-100">
                            <td class="px-4 sm:px-5 py-3 text-zinc-800 pl-8 sm:pl-10">{{ $row['description'] }}</td>
                            <td class="px-4 sm:px-5 py-3 text-center font-sans text-zinc-600">
                                {{ number_format($row['percentage'], 2, '.', '') }}%
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-left font-sans text-zinc-800">
                                Rp {{ number_format($row['amount'], 0, ',', '.') }}
                            </td>
                            @if($this->canEdit)
                                <td class="px-4 sm:px-5 py-3 text-center whitespace-nowrap">
                                    <div class="flex flex-row justify-center gap-1.5 items-center">
                                        <button wire:click="openEdit('{{ $row['description'] }}')"
                                            type="button"
                                            style="background-color:#fbbf24;color:#1c1917;"
                                            title="Edit"
                                            class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                                <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                                <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                            </svg>
                                        </button>
                                        <flux:button wire:click="confirmDelete('{{ $row['description'] }}')"
                                            variant="danger" size="xs" icon="trash" title="Hapus" />
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr class="border-b border-zinc-100">
                            <td class="px-4 sm:px-5 py-3 text-zinc-400 italic text-xs pl-8 sm:pl-10" colspan="{{ $this->canEdit ? 4 : 3 }}">
                                — Belum ada pos Pengurang —
                            </td>
                        </tr>
                    @endforelse

                    {{-- ── Profit row after deductions ─────────────────────── --}}
                    <tr class="bg-zinc-100 border-y-2 border-zinc-300">
                        <td class="px-4 sm:px-5 py-3.5 font-bold text-zinc-900" colspan="2">
                            Laba/Rugi Bersih setelah {{ $this->deductionLabel }}
                        </td>
                        <td class="px-4 sm:px-5 py-3.5 text-left font-sans font-bold {{ $this->netIncomeAfterDeductions >= 0 ? 'text-brand-800' : 'text-red-700' }}">
                            Rp {{ number_format($this->netIncomeAfterDeductions, 0, ',', '.') }}
                        </td>
                        @if($this->canEdit)<td></td>@endif
                    </tr>

                    {{-- ── Articles of Association header section ────────────────────────────── --}}
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <td class="px-4 sm:px-5 py-2.5 text-xs font-bold text-zinc-500 uppercase tracking-wider" colspan="{{ $this->canEdit ? 4 : 3 }}">
                            Alokasi Laba Bersih sesuai AD/ART
                        </td>
                    </tr>

                    {{-- ── Articles of Association section ───────────────────────────────────── --}}
                    @forelse ($this->adArtRows as $row)
                        <tr class="hover:bg-zinc-50 transition-colors border-b border-zinc-100">
                            <td class="px-4 sm:px-5 py-3 text-zinc-800 pl-8 sm:pl-10">{{ $row['description'] }}</td>
                            <td class="px-4 sm:px-5 py-3 text-center font-sans text-zinc-600">
                                {{ number_format($row['percentage'], 2, '.', '') }}%
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-left font-sans text-zinc-800">
                                Rp {{ number_format($row['amount'], 0, ',', '.') }}
                            </td>
                            @if($this->canEdit)
                                <td class="px-4 sm:px-5 py-3 text-center whitespace-nowrap">
                                    <div class="flex flex-row justify-center gap-1.5 items-center">
                                        <button wire:click="openEdit('{{ $row['description'] }}')"
                                            type="button"
                                            style="background-color:#fbbf24;color:#1c1917;"
                                            title="Edit"
                                            class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                                <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                                <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                            </svg>
                                        </button>
                                        <flux:button wire:click="confirmDelete('{{ $row['description'] }}')"
                                            variant="danger" size="xs" icon="trash" title="Hapus" />
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr class="border-b border-zinc-100">
                            <td class="px-4 sm:px-5 py-3 text-zinc-400 italic text-xs pl-8 sm:pl-10" colspan="{{ $this->canEdit ? 4 : 3 }}">
                                — Belum ada pos AD/ART —
                            </td>
                        </tr>
                    @endforelse

                    {{-- ── Total Articles of Association allocation ─────────────────────────────────────── --}}
                    @if($this->adArtRows->count() > 0)
                        <tr class="bg-zinc-50 border-t-2 border-zinc-300">
                            <td class="px-4 sm:px-5 py-3.5 font-bold text-zinc-900 text-left">TOTAL AD/ART</td>
                            <td class="px-4 sm:px-5 py-3.5 text-center font-sans font-bold {{ $this->totalAdArtPercent > 100 ? 'text-red-600' : 'text-brand-700' }}">
                                {{ number_format($this->totalAdArtPercent, 2, '.', '') }}%
                                @if($this->totalAdArtPercent > 100)
                                    <div class="text-[10px] text-red-500 font-sans font-normal">Melebihi 100%</div>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-left font-sans font-bold text-zinc-900">
                                Rp {{ number_format($this->totalAdArtAmount, 0, ',', '.') }}
                            </td>
                            @if($this->canEdit)<td></td>@endif
                        </tr>
                    @endif

                    {{-- Empty state when there is no data at all --}}
                    @if($this->allocationRows->count() === 0)
                        <tr>
                            <td colspan="{{ $this->canEdit ? 4 : 3 }}" class="px-5 py-8 text-center text-zinc-500">
                                Belum ada pos alokasi yang diatur. Silakan tambah alokasi.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    {{-- Add/edit allocation modal --}}
    @if($showModal && $this->canEdit)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="$wire.closeModal()">
            <div wire:click="closeModal" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden z-10">
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50">
                    <h2 class="text-base font-semibold text-zinc-900">{{ $editingDescription ? 'Edit Alokasi' : 'Tambah Alokasi' }}</h2>
                    <button wire:click="closeModal" class="size-8 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 transition-colors">
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="saveRow" class="px-6 py-5 flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-zinc-700">Keterangan (Pos Alokasi) <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="formDescription" placeholder="Contoh: Pajak, Dana Desa, Bonus Pengurus..." autofocus
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('formDescription') border-red-400 @enderror">
                        @error('formDescription') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-zinc-700">Kelompok Alokasi</label>
                            <select wire:model.live="formGroup"
                                class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                                <option value="pengurang">Laba Bersih</option>
                                <option value="ad_art">AD/ART</option>
                            </select>
                            @error('formGroup') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-zinc-700">Persentase (%) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="100" wire:model="formPercentage" placeholder="12.5"
                                class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none @error('formPercentage') border-red-400 @enderror">
                            @error('formPercentage') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="text-xs text-zinc-600 bg-brand-50/60 rounded-xl px-4 py-2.5 border border-brand-200">
                        @if($formGroup === 'pengurang')
                            <span class="font-semibold text-brand-900">Laba Bersih:</span>
                            Nilai pos ini dihitung dari total Laba Bersih (Rp {{ number_format($this->netIncome, 0, ',', '.') }}), misalnya pajak atau iuran wajib.
                        @else
                            <span class="font-semibold text-brand-900">AD/ART:</span>
                            Nilai pos ini dihitung dari sisa Laba setelah semua potongan (Rp {{ number_format($this->netIncomeAfterDeductions, 0, ',', '.') }}), sesuai pembagian yang tercantum di AD/ART.
                        @endif
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" wire:click="closeModal" class="flex-1 py-2.5 rounded-xl border-2 border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveRow" class="flex-1 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="saveRow">{{ $editingDescription ? 'Simpan Perubahan' : 'Tambah Alokasi' }}</span>
                            <span wire:loading wire:target="saveRow">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">Konfirmasi Hapus</h2>
                        <div class="mt-2 text-sm text-zinc-600">
                            <p>Yakin ingin menghapus data ini?</p>
                            <p>Data yang dihapus tidak dapat dikembalikan dan mungkin mempengaruhi kalkulasi laporan.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
