<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-20">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-zinc-900">Catat Pengeluaran</h1>
            <p class="text-sm text-zinc-500">
                Riwayat pengeluaran operasional
                <span class="font-semibold text-brand-700">BUMDes</span>.
            </p>
        </div>
        <button type="button" wire:click="openCreateModal"
            class="flex items-center justify-center gap-1.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2.5 rounded-xl transition-all shadow-sm shrink-0">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Pengeluaran
        </button>
    </div>

    {{-- Error global --}}
    @error('items')
        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">
            {{ $message }}
        </div>
    @enderror

    {{-- Riwayat Pengeluaran (Catatan Pengeluaran) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-zinc-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-zinc-100 bg-zinc-50">
            <div class="flex flex-col gap-4">
                {{-- Title row --}}
                <div class="flex items-center gap-2">
                    <svg class="size-5 text-zinc-500 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <h2 class="text-base font-semibold text-zinc-800">Riwayat Pengeluaran</h2>
                </div>

                {{-- Filter controls --}}
                <div
                    class="flex flex-col md:flex-row gap-5 md:items-end p-4 bg-white rounded-xl shadow-sm border border-brand-100">

                    {{-- Mode selector --}}
                    <div class="flex flex-col gap-1.5 w-full md:w-auto">
                        <label for="filterMode" class="text-xs font-semibold text-zinc-600">Periode</label>
                        <select id="filterMode" wire:model.live="filterMode"
                            class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                            <option value="harian">Harian</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="semester">Semester</option>
                            <option value="tahunan">Tahunan</option>
                        </select>
                    </div>

                    {{-- Date/Period picker based on filterMode --}}
                    <div class="flex flex-col gap-1.5 w-full md:w-auto md:min-w-48">
                        {{-- Harian --}}
                        @if ($filterMode === 'harian')
                            <label for="filterDate" class="text-xs font-semibold text-zinc-600">Tanggal</label>
                            <div wire:ignore wire:key="picker-harian" x-data="{ val: $wire.entangle('filterDate').live }">
                                <input type="text" x-model="val" x-init="window.flatpickr($el, {
                                    locale: window.flatpickrIndonesian,
                                    dateFormat: 'Y-m-d',
                                    defaultDate: val,
                                    altInput: true,
                                    altFormat: 'd F Y',
                                    disableMobile: true
                                })"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors cursor-pointer" />
                            </div>

                            {{-- Bulanan --}}
                        @elseif($filterMode === 'bulanan')
                            <label for="filterBulan" class="text-xs font-semibold text-zinc-600">Bulan</label>
                            <div wire:ignore wire:key="picker-bulanan" x-data="{ val: $wire.entangle('filterBulan').live }">
                                <input type="text" x-model="val" x-init="window.flatpickr($el, {
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

                            {{-- Semester --}}
                        @elseif($filterMode === 'semester')
                            <div wire:key="picker-semester" class="flex flex-col sm:flex-row gap-5">
                                <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                                    <label for="filterSemester" class="text-xs font-semibold text-zinc-600">Semester</label>
                                    <select wire:model.live="filterSemester"
                                        class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                                        <option value="1">1 (Jan-Jun)</option>
                                        <option value="2">2 (Jul-Des)</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                                    <label for="filterSemesterTahun" class="text-xs font-semibold text-zinc-600">Tahun</label>
                                    <select wire:model.live="filterSemesterTahun"
                                        class="w-full sm:min-w-32 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            {{-- Tahunan --}}
                        @elseif($filterMode === 'tahunan')
                            <div wire:key="picker-tahunan">
                                <label for="filterTahun" class="text-xs font-semibold text-zinc-600">Tahun</label>
                                <select wire:model.live="filterTahun"
                                    class="w-full sm:min-w-40 rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors">
                                    @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto hidden md:block">
            <table class="w-full">
                <thead class="bg-zinc-50 border-b border-zinc-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-600 uppercase tracking-wider">Tanggal
                        </th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-600 uppercase tracking-wider">No.
                            Bukti</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-600 uppercase tracking-wider">
                            Keterangan</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-600 uppercase tracking-wider">Akun
                        </th>
                        <th class="py-3 px-4 text-right text-xs font-bold text-zinc-600 uppercase tracking-wider">
                            Nominal</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-zinc-600 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($riwayat as $jurnal)
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap text-sm text-zinc-700">
                                {{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span
                                    class="text-xs font-mono font-semibold text-zinc-600">{{ $jurnal->nomor_bukti }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-sm text-zinc-700 line-clamp-2">{{ $jurnal->keterangan }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-1 rounded-md text-xs font-bold bg-brand-50 text-brand-700 border border-brand-100 font-mono">
                                        {{ $jurnal->kodeAkun->kode }}
                                    </span>
                                    <span class="text-sm text-zinc-600 truncate max-w-[160px]"
                                        title="{{ $jurnal->kodeAkun->nama }}">
                                        {{ $jurnal->kodeAkun->nama }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-red-600">Rp
                                    {{ number_format($jurnal->debet, 0, ',', '.') }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="editRiwayat('{{ $jurnal->nomor_bukti }}')"
                                        class="p-2 text-brand-600 hover:text-brand-800 rounded-lg transition-colors inline-flex items-center justify-center"
                                        title="Edit">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $jurnal->nomor_bukti }}')"
                                        class="p-2 text-red-500 hover:text-red-700 rounded-lg transition-colors inline-flex items-center justify-center"
                                        title="Hapus">
                                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-zinc-500 text-sm">
                                <svg class="size-10 text-zinc-300 mx-auto" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="mt-2">Belum ada catatan pengeluaran pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($riwayatTotal > 0)
                    <tfoot>
                        <tr class="bg-brand-50/40 border-t-2 border-brand-200">
                            <td colspan="4" class="py-3.5 px-4 text-right text-sm font-bold text-zinc-700">Total
                                Pengeluaran</td>
                            <td class="py-3.5 px-4 text-right text-base font-extrabold text-red-600 whitespace-nowrap">
                                Rp {{ number_format($riwayatTotal, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-zinc-100">
            @forelse($riwayat as $jurnal)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($jurnal->tanggal)->translatedFormat('d M Y') }}</span>
                        <span class="text-xs font-mono text-zinc-500">{{ $jurnal->nomor_bukti }}</span>
                    </div>
                    <p class="text-sm text-zinc-700">{{ $jurnal->keterangan }}</p>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-brand-50 text-brand-700 border border-brand-100 font-mono">
                            {{ $jurnal->kodeAkun->kode }}
                        </span>
                        <span class="text-xs text-zinc-600 truncate">{{ $jurnal->kodeAkun->nama }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-bold text-red-600">Rp {{ number_format($jurnal->debet, 0, ',', '.') }}</span>
                        <div class="flex items-center gap-1">
                            <button wire:click="editRiwayat('{{ $jurnal->nomor_bukti }}')"
                                class="p-2 text-brand-600 hover:bg-brand-50 rounded-lg transition-colors"
                                title="Edit">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </button>
                            <button wire:click="confirmDelete('{{ $jurnal->nomor_bukti }}')"
                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                title="Hapus">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-zinc-500 text-sm">
                    Belum ada catatan pengeluaran pada periode ini.
                </div>
            @endforelse
        </div>

        {{-- Mobile total --}}
        @if ($riwayatTotal > 0)
            <div
                class="md:hidden bg-brand-50 border-t border-brand-200 p-4 flex items-center justify-between">
                <span class="text-sm font-bold text-zinc-700">Total Pengeluaran</span>
                <span class="text-base font-extrabold text-red-600">Rp
                    {{ number_format($riwayatTotal, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    {{-- ============================ CREATE MODAL ============================ --}}
    @if ($showCreateModal)
        <div x-data x-init="document.body.style.overflow='hidden'" x-effect="document.body.style.overflow = $wire.showCreateModal ? 'hidden' : ''"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="create-modal-title">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeCreateModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 bg-zinc-50 rounded-t-lg">
                        <h3 id="create-modal-title" class="text-lg font-semibold text-zinc-900">Tambah Pengeluaran</h3>
                        <button type="button" wire:click="closeCreateModal"
                            class="p-1.5 text-zinc-400 hover:text-zinc-600 rounded-lg transition-colors"
                            aria-label="Tutup modal">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form wire:submit.prevent="submit" class="p-6 space-y-5">
                        @error('items')
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal</label>
                            <div x-data="{
                                    value: $wire.entangle('tanggal'),
                                    fp: null,
                                    init() {
                                        this.fp = flatpickr(this.$refs.dateInput, {
                                            dateFormat: 'Y-m-d',
                                            locale: window.flatpickrIndonesian,
                                            altInput: true,
                                            altFormat: 'd F Y',
                                            defaultDate: this.value ? this.value : new Date(),
                                            onChange: (selectedDates, dateStr) => {
                                                if (selectedDates.length > 0) {
                                                    this.value = window.flatpickr.formatDate(selectedDates[0], 'Y-m-d');
                                                } else {
                                                    this.value = null;
                                                }
                                            }
                                        });
                                        this.$watch('value', (val) => {
                                            if (val && this.fp) {
                                                this.fp.setDate(val, false);
                                            } else if (!val && this.fp) {
                                                this.fp.clear();
                                            }
                                        });
                                    }
                                }" wire:ignore wire:key="create-datepicker-{{ $showCreateModal ? 'on' : 'off' }}">
                                <input type="text" id="tanggal" placeholder="Pilih tanggal"
                                    x-ref="dateInput"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors" />
                            </div>
                            @error('tanggal')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-3">
                            @foreach($items as $index => $item)
                                <div class="p-3 border border-zinc-200 rounded-xl space-y-3 bg-zinc-50/50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-zinc-600 uppercase">Item {{ $index + 1 }}</span>
                                        @if(count($items) > 1)
                                            <button type="button" wire:click="removeItem({{ $index }})"
                                                class="p-1 text-zinc-400 hover:text-red-500 rounded transition-colors"
                                                aria-label="Hapus item">
                                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Jenis Biaya</label>
                                        <div x-data="{
                                            open: false,
                                            selectedId: @entangle('items.'.$index.'.kode_akun_id'),
                                            options: [
                                                @foreach($akunBiaya as $akun)
                                                { id: '{{ $akun->id }}', label: '{{ $akun->kode }} - {{ $akun->nama }}' },
                                                @endforeach
                                            ],
                                            get selectedLabel() {
                                                const selected = this.options.find(o => o.id == this.selectedId);
                                                return selected ? selected.label : 'Pilih jenis biaya';
                                            }
                                        }" class="relative">
                                            <button type="button" @click="open = !open" @click.outside="open = false"
                                                class="w-full flex items-center justify-between rounded-xl border-2 border-zinc-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors"
                                                :class="selectedId ? 'text-zinc-900' : 'text-zinc-500'">
                                                <span x-text="selectedLabel" class="truncate"></span>
                                                <svg class="size-4 text-zinc-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-transition style="display: none;"
                                                class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                                <ul class="py-1">
                                                    <template x-for="option in options" :key="option.id">
                                                        <li>
                                                            <button type="button" @click="selectedId = option.id; open = false"
                                                                class="w-full text-left px-4 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 transition-colors"
                                                                :class="selectedId == option.id ? 'bg-brand-50 text-brand-700 font-medium' : 'text-zinc-700'"
                                                                x-text="option.label"></button>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                            <select wire:model="items.{{ $index }}.kode_akun_id" class="hidden" required>
                                                <option value="">Pilih</option>
                                                @foreach($akunBiaya as $akun)
                                                    <option value="{{ $akun->id }}">{{ $akun->kode }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error("items.{$index}.kode_akun_id")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Nominal</label>
                                        <div class="relative">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-500 font-medium pointer-events-none">Rp</span>
                                            <input type="number" id="nominal-{{ $index }}" wire:model.live="items.{{ $index }}.nominal" min="1" max="9999999999999" step="1" placeholder="0"
                                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 pl-10 pr-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors"
                                                required />
                                        </div>
                                        @error("items.{$index}.nominal")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">Keterangan</label>
                                        <textarea wire:model="items.{{ $index }}.keterangan" rows="2"
                                            placeholder="Masukkan keterangan pengeluaran"
                                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors resize-none"
                                            required></textarea>
                                        @error("items.{$index}.keterangan")
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" wire:click="addItem"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-brand-600 bg-brand-50 border border-brand-200 rounded-xl hover:bg-brand-100 transition-colors">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Item
                        </button>

                        <div class="pt-4 border-t border-zinc-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700">Total</span>
                                <span class="text-lg font-extrabold text-red-600">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                <button type="button" wire:click="closeCreateModal"
                                    class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition-colors shadow-sm">
                                    Simpan Pengeluaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================ EDIT MODAL ============================ --}}
    @if ($showEditModal)
        <div x-data x-init="document.body.style.overflow='hidden'" x-effect="document.body.style.overflow = $wire.showEditModal ? 'hidden' : ''"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="edit-modal-title">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeEditModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100 bg-zinc-50 rounded-t-lg">
                        <h3 id="edit-modal-title" class="text-lg font-semibold text-zinc-900">Edit Pengeluaran</h3>
                        <button type="button" wire:click="closeEditModal"
                            class="p-1.5 text-zinc-400 hover:text-zinc-600 rounded-lg transition-colors"
                            aria-label="Tutup modal">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form wire:submit.prevent="updateRiwayat" class="p-6 space-y-5">
                        @error('editKodeAkunId')
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $message }}</div>
                        @enderror
                        @error('editKeterangan')
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $message }}</div>
                        @enderror
                        @error('editNominal')
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $message }}</div>
                        @enderror
                        @error('editTanggal')
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">{{ $message }}</div>
                        @enderror

                        <div>
                            <label for="editTanggal" class="block text-sm font-medium text-zinc-700 mb-1.5">Tanggal</label>
                            <div x-data="{
                                    value: $wire.entangle('editTanggal'),
                                    fp: null,
                                    init() {
                                        this.fp = flatpickr(this.$refs.dateInput, {
                                            dateFormat: 'Y-m-d',
                                            locale: window.flatpickrIndonesian,
                                            altInput: true,
                                            altFormat: 'd F Y',
                                            defaultDate: this.value ? this.value : new Date(),
                                            onChange: (selectedDates, dateStr) => {
                                                if (selectedDates.length > 0) {
                                                    this.value = window.flatpickr.formatDate(selectedDates[0], 'Y-m-d');
                                                } else {
                                                    this.value = null;
                                                }
                                            }
                                        });
                                        this.$watch('value', (val) => {
                                            if (val && this.fp) {
                                                this.fp.setDate(val, false);
                                            } else if (!val && this.fp) {
                                                this.fp.clear();
                                            }
                                        });
                                    }
                                }" wire:ignore wire:key="edit-datepicker-{{ $showEditModal ? 'on' : 'off' }}">
                                <input type="text" id="editTanggal" placeholder="Pilih tanggal"
                                    x-ref="dateInput"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors" />
                            </div>
                        </div>
                        <div>
                            <label for="editKodeAkunId" class="block text-sm font-medium text-zinc-700 mb-1.5">Jenis Biaya</label>
                            <div x-data="{
                                open: false,
                                selectedId: @entangle('editKodeAkunId'),
                                options: [
                                    @foreach($akunBiaya as $akun)
                                    { id: '{{ $akun->id }}', label: '{{ $akun->kode }} - {{ $akun->nama }}' },
                                    @endforeach
                                ],
                                get selectedLabel() {
                                    const selected = this.options.find(o => o.id == this.selectedId);
                                    return selected ? selected.label : 'Pilih jenis biaya';
                                }
                            }" class="relative">
                                <button type="button" @click="open = !open" @click.outside="open = false"
                                    class="w-full flex items-center justify-between rounded-xl border-2 border-zinc-200 bg-white px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors"
                                    :class="selectedId ? 'text-zinc-900' : 'text-zinc-500'">
                                    <span x-text="selectedLabel" class="truncate"></span>
                                    <svg class="size-4 text-zinc-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" x-transition style="display: none;"
                                    class="absolute z-10 w-full mt-1 bg-white border border-zinc-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                                    <ul class="py-1">
                                        <template x-for="option in options" :key="option.id">
                                            <li>
                                                <button type="button" @click="selectedId = option.id; open = false"
                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 transition-colors"
                                                    :class="selectedId == option.id ? 'bg-brand-50 text-brand-700 font-medium' : 'text-zinc-700'"
                                                    x-text="option.label"></button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <select wire:model="editKodeAkunId" id="editKodeAkunId" class="hidden" required>
                                    <option value="">Pilih</option>
                                    @foreach($akunBiaya as $akun)
                                        <option value="{{ $akun->id }}">{{ $akun->kode }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="editKeterangan" class="block text-sm font-medium text-zinc-700 mb-1.5">Keterangan</label>
                            <textarea wire:model="editKeterangan" id="editKeterangan" rows="3"
                                placeholder="Masukkan keterangan pengeluaran"
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors resize-none"
                                required></textarea>
                        </div>
                        <div>
                            <label for="editNominal" class="block text-sm font-medium text-zinc-700 mb-1.5">Nominal</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-zinc-500 font-medium pointer-events-none">Rp</span>
                                <input type="number" id="editNominal" wire:model.live="editNominal" min="1" max="9999999999999" step="1" placeholder="0"
                                    class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 pl-10 pr-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors"
                                    required />
                            </div>
                        </div>
                        <div class="pt-4 border-t border-zinc-100 flex flex-col sm:flex-row gap-3">
                            <button type="button" wire:click="closeEditModal"
                                class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition-colors shadow-sm">
                                Perbarui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================ DELETE MODAL ============================ --}}
    @if ($showDeleteModal)
        <div x-data x-init="document.body.style.overflow='hidden'" x-effect="document.body.style.overflow = $wire.showDeleteModal ? 'hidden' : ''"
            class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeDeleteModal" aria-hidden="true"></div>
                <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">
                    <div class="px-6 py-6 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <svg class="size-7" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                        <h3 id="delete-modal-title" class="text-lg font-semibold text-zinc-900 mb-2">Hapus Pengeluaran?</h3>
                        <p class="text-zinc-600 text-sm">
                            Apakah Anda yakin ingin menghapus data pengeluaran ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex flex-col-reverse sm:flex-row gap-3 sm:justify-end rounded-b-lg">
                        <button type="button" wire:click="closeDeleteModal"
                            class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete"
                            class="flex-1 sm:flex-none px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-sm">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
