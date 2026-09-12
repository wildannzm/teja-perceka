<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-20">

    <x-page-header title="Alokasi Laba" description="Hitung alokasi dari Laba Bersih berdasarkan persentase.">
        <x-slot:actions>
            <div class="flex items-center gap-3 w-full md:w-auto">
            <button type="button" wire:click="exportPdf"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border-2 border-brand-200 text-brand-700 rounded-xl text-sm w-full md:w-auto justify-center font-bold hover:bg-brand-50 hover:border-brand-300 transition-all active:scale-95 shadow-sm">
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Cetak PDF
            </button>
        </x-slot:actions>
    </x-page-header>
    </div>

    {{-- Filter panel --}}
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-brand-100 flex flex-col md:flex-row gap-4 items-end">
        
        <div class="flex flex-col gap-1.5 w-full md:w-auto min-w-[200px]">
            <label class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Unit Wisata</label>
            <select wire:model.live="unit_id"
                class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                <option value="">Semua Unit (Konsolidasi)</option>
                @foreach ($this->units as $u)
                    <option value="{{ $u->id }}">{{ $u->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-col gap-1.5 w-full md:w-auto min-w-[150px]">
            <label class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Mode Laporan</label>
            <select wire:model.live="mode"
                class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                <option value="bulanan">Bulanan</option>
                <option value="semester">Semester</option>
                <option value="tahunan">Tahunan</option>
            </select>
        </div>

        <div class="flex flex-col gap-1.5 w-full md:w-auto min-w-[180px]">
            <label class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                @if($mode === 'bulanan') Bulan
                @elseif($mode === 'semester') Semester
                @else Tahun @endif
            </label>
            @if ($mode === 'bulanan')
                <div wire:ignore x-data="{ val: $wire.entangle('periode').live }">
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
        class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer" />
</div>
            @elseif ($mode === 'semester')
                <div class="flex gap-2">
                    <select wire:model.live="semester"
                        class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                        <option value="1">Sem 1 (Jan-Jun)</option>
                        <option value="2">Sem 2 (Jul-Des)</option>
                    </select>
                    <input type="number" wire:model.live="semesterTahun" placeholder="Tahun"
                        class="w-24 rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                </div>
            @else
                <input type="number" wire:model.live="periode" placeholder="Pilih Tahun"
                    class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
            @endif
        </div>
    </div>

    {{-- Net profit info --}}
    <div class="bg-gradient-to-br from-brand-600 to-brand-700 p-6 rounded-2xl shadow-sm text-white flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-brand-100 font-semibold mb-1">Total Laba Bersih</h2>
            <p class="text-sm text-brand-200">
                {{ $this->selectedUnit ? 'Unit: ' . $this->selectedUnit->nama : 'Semua Unit (Konsolidasi)' }}
                &bull;
                {{ $mode === 'bulanan' ? \Carbon\Carbon::parse($periode)->translatedFormat('F Y') : ($mode === 'semester' ? 'Semester ' . $semester . ' ' . $semesterTahun : 'Tahun ' . $periode) }}
            </p>
        </div>
        <div class="text-2xl sm:text-3xl font-bold font-mono tracking-tight bg-white/20 px-6 py-3 rounded-xl border border-white/30 backdrop-blur-sm whitespace-nowrap">
            Rp {{ number_format($this->labaBersih, 0, ',', '.') }}
        </div>
    </div>

    {{-- Allocation table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-brand-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-brand-100 flex items-center justify-between bg-zinc-50/50">
            <h3 class="font-bold text-zinc-900">Rincian Alokasi</h3>
            
            @if($this->canEdit)
                <button type="button" wire:click="$toggle('showForm')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-100 text-brand-700 rounded-lg text-xs font-bold hover:bg-brand-200 transition-colors">
                    @if($showForm)
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    @else
                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Alokasi
                    @endif
                </button>
            @endif
        </div>

        {{-- Add form --}}
        @if($showForm && $this->canEdit)
            <div class="p-4 sm:p-5 border-b border-brand-100 bg-brand-50/30">
                <form wire:submit.prevent="simpanBaris" class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        {{-- Description --}}
                        <div class="flex flex-col gap-1.5 flex-1">
                            <label class="text-xs font-bold text-zinc-700">Keterangan (Pos Alokasi)</label>
                            <input type="text" wire:model="formKeterangan" placeholder="Contoh: Pajak, Dana Desa, Bonus Pengurus..."
                                class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none">
                            @error('formKeterangan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Group --}}
                        <div class="flex flex-col gap-1.5 w-full sm:w-52">
                            <label class="text-xs font-bold text-zinc-700">Kelompok</label>
                            <select wire:model="formKelompok"
                                class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer">
                                <option value="pengurang">Pengurang (dari Laba Bersih)</option>
                                <option value="ad_art">AD/ART (dari Laba Setelah Pengurang)</option>
                            </select>
                            @error('formKelompok') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Percentage --}}
                        <div class="flex flex-col gap-1.5 w-full sm:w-36">
                            <label class="text-xs font-bold text-zinc-700">Persentase (%)</label>
                            <input type="number" step="0.01" wire:model="formPersentase" placeholder="Contoh: 12.5"
                                class="w-full rounded-xl border-2 border-zinc-200 px-3 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none">
                            @error('formPersentase') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Contextual info --}}
                    <div class="text-xs text-zinc-500 bg-zinc-50 rounded-lg px-3 py-2 border border-zinc-200">
                        @if($formKelompok === 'pengurang')
                            <span class="font-semibold text-zinc-700">Pengurang:</span>
                            Nominal dihitung dari Laba Bersih (Rp {{ number_format($this->labaBersih, 0, ',', '.') }}).
                        @else
                            <span class="font-semibold text-zinc-700">AD/ART:</span>
                            Nominal dihitung dari Laba Bersih setelah Pengurang (Rp {{ number_format($this->labaSetelahPengurang, 0, ',', '.') }}).
                        @endif
                    </div>

                    <div>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-bold hover:bg-brand-700 transition-colors shadow-sm active:scale-95">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-zinc-500 font-semibold">
                    <tr>
                        <th class="px-4 sm:px-5 py-3">Keterangan</th>
                        <th class="px-4 sm:px-5 py-3 text-right">%</th>
                        <th class="px-4 sm:px-5 py-3 text-right">Nominal (Rp)</th>
                        @if($this->canEdit)
                            <th class="px-4 sm:px-5 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    {{-- ── Initial NET PROFIT ─────────────────────────────────── --}}
                    <tr class="bg-brand-50 border-b border-brand-100">
                        <td class="px-4 sm:px-5 py-3.5 font-bold text-brand-900" colspan="2">LABA BERSIH</td>
                        <td class="px-4 sm:px-5 py-3.5 text-right font-mono font-bold text-brand-900">
                            {{ number_format($this->labaBersih, 0, ',', '.') }}
                        </td>
                        @if($this->canEdit)<td></td>@endif
                    </tr>

                    {{-- ── Deduction section ────────────────────────────────── --}}
                    @forelse ($this->pengurangRows as $row)
                        <tr class="hover:bg-zinc-50 transition-colors border-b border-zinc-100">
                            <td class="px-4 sm:px-5 py-3 text-zinc-800 pl-8 sm:pl-10">{{ $row['keterangan'] }}</td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-zinc-600">
                                {{ number_format($row['persentase'], 2, ',', '.') }}%
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-zinc-800">
                                ({{ number_format($row['nominal'], 0, ',', '.') }})
                            </td>
                            @if($this->canEdit)
                                <td class="px-4 sm:px-5 py-3 text-right">
                                    <button type="button" wire:click="confirmDelete('{{ $row['keterangan'] }}')"
                                        class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2 py-1 rounded-md transition-colors inline-flex items-center gap-1 text-xs font-semibold whitespace-nowrap">
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
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
                            Laba/Rugi Bersih setelah Pengurang
                        </td>
                        <td class="px-4 sm:px-5 py-3.5 text-right font-mono font-bold {{ $this->labaSetelahPengurang >= 0 ? 'text-brand-800' : 'text-red-700' }}">
                            {{ number_format($this->labaSetelahPengurang, 0, ',', '.') }}
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
                            <td class="px-4 sm:px-5 py-3 text-zinc-800 pl-8 sm:pl-10">{{ $row['keterangan'] }}</td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-zinc-600">
                                {{ number_format($row['persentase'], 2, ',', '.') }}%
                            </td>
                            <td class="px-4 sm:px-5 py-3 text-right font-mono text-zinc-800">
                                {{ number_format($row['nominal'], 0, ',', '.') }}
                            </td>
                            @if($this->canEdit)
                                <td class="px-4 sm:px-5 py-3 text-right">
                                    <button type="button" wire:click="confirmDelete('{{ $row['keterangan'] }}')"
                                        class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2 py-1 rounded-md transition-colors inline-flex items-center gap-1 text-xs font-semibold whitespace-nowrap">
                                        <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        Hapus
                                    </button>
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
                            <td class="px-4 sm:px-5 py-3.5 font-bold text-zinc-900 text-right">TOTAL AD/ART</td>
                            <td class="px-4 sm:px-5 py-3.5 text-right font-mono font-bold {{ $this->totalAdArtPersen > 100 ? 'text-red-600' : 'text-brand-700' }}">
                                {{ number_format($this->totalAdArtPersen, 2, ',', '.') }}%
                                @if($this->totalAdArtPersen > 100)
                                    <div class="text-[10px] text-red-500 font-sans font-normal">Melebihi 100%</div>
                                @endif
                            </td>
                            <td class="px-4 sm:px-5 py-3.5 text-right font-mono font-bold text-zinc-900">
                                {{ number_format($this->totalAdArt, 0, ',', '.') }}
                            </td>
                            @if($this->canEdit)<td></td>@endif
                        </tr>
                    @endif

                    {{-- Empty state when there is no data at all --}}
                    @if($this->alokasiRows->count() === 0 && !$showForm)
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
