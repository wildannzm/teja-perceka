<div>
    <div class="flex h-full w-full flex-col gap-6 max-w-7xl mx-auto pb-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-zinc-900">Laporan Jurnal Umum</h1>
            <button wire:click="exportPdf" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white transition-all bg-brand-500 border border-transparent rounded-xl hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 shadow-sm w-full sm:w-auto">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Ekspor PDF
            </button>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-zinc-200 shadow-sm print:hidden">
            <h2 class="text-base font-bold mb-4 text-zinc-900 flex items-center gap-2">
                <svg class="size-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                Filter Laporan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5 mb-1">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Pilih Unit Usaha</label>
                    <select wire:model.live="unit_id" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-sm py-2.5 px-3.5 bg-white">
                        <option value="">Semua Unit</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Tanggal Mulai</label>
                    <div wire:ignore x-data="{ val: $wire.entangle('start_date').live }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            dateFormat: 'Y-m-d',
            defaultDate: val,
            altInput: true,
            altFormat: 'd F Y',
            disableMobile: true
        })"
        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-sm py-2.5 px-3.5 bg-white cursor-pointer" />
</div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Tanggal Selesai</label>
                    <div wire:ignore x-data="{ val: $wire.entangle('end_date').live }">
    <input type="text" x-model="val"
        x-init="window.flatpickr($el, {
            locale: window.flatpickrIndonesian,
            dateFormat: 'Y-m-d',
            defaultDate: val,
            altInput: true,
            altFormat: 'd F Y',
            disableMobile: true
        })"
        class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-sm py-2.5 px-3.5 bg-white cursor-pointer" />
</div>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-zinc-200 shadow-sm flex-1">
            <h2 class="text-base font-bold mb-4 text-zinc-900 flex items-center gap-2">
                <svg class="size-5 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                Pratinjau Jurnal Umum
            </h2>
            <div class="overflow-x-auto rounded-xl border border-zinc-200">
                <table class="w-full text-sm text-left text-zinc-600">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Tanggal</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Unit Usaha</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Keterangan</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Akun (COA)</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap">Debit</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap">Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($journals as $nomorBukti => $group)
                            @foreach ($group as $jurnal)
                                <tr class="bg-white hover:bg-zinc-50 transition-colors">
                                    @if ($loop->first)
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-5 py-4 align-top whitespace-nowrap font-medium text-zinc-900 border-r border-zinc-100">
                                            {{ $jurnal->tanggal->translatedFormat('d F Y') }}</td>
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-5 py-4 align-top border-r border-zinc-100 min-w-[120px]">
                                            {{ $jurnal->unitWisata->nama ?? '-' }}</td>
                                        <td rowspan="{{ $group->count() }}"
                                            class="px-5 py-4 align-top border-r border-zinc-100 min-w-[150px]">{{ $jurnal->keterangan }}</td>
                                    @endif
                                    <td class="px-5 py-4 min-w-[180px]">
                                        <span class="font-medium text-zinc-700">{{ $jurnal->kodeAkun->kode ?? '-' }}</span> -
                                        {{ $jurnal->kodeAkun->nama ?? 'Akun Tidak Ditemukan' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $jurnal->debet > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                        {{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $jurnal->kredit > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                        {{ $jurnal->kredit > 0 ? number_format($jurnal->kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-zinc-500">
                                    <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Belum ada data transaksi yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
