<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">Cetak Laporan BUMDes</h1>
            <flux:button variant="primary" icon="printer" onclick="window.print()">Cetak Laporan</flux:button>
        </div>

        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 print:hidden">
            <h2 class="text-lg font-semibold mb-4 text-neutral-900 dark:text-white">Filter Laporan</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                <flux:select wire:model.live="unit_id" label="Pilih Unit Usaha">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model.live="start_date" type="date" label="Tanggal Mulai" />
                <flux:input wire:model.live="end_date" type="date" label="Tanggal Selesai" />
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 flex-1">
            <h2 class="text-lg font-semibold mb-4 text-neutral-900 dark:text-white">Pratinjau Jurnal Umum</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">
                    <thead class="text-xs text-neutral-700 uppercase bg-neutral-50 dark:bg-neutral-800 dark:text-neutral-300">
                        <tr>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Tanggal</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Unit Usaha</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Keterangan</th>
                            <th scope="col" class="px-4 py-3 border-b dark:border-neutral-700">Akun (COA)</th>
                            <th scope="col" class="px-4 py-3 text-right border-b dark:border-neutral-700">Debet</th>
                            <th scope="col" class="px-4 py-3 text-right border-b dark:border-neutral-700">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($journals as $nomorBukti => $group)
                            @foreach($group as $jurnal)
                            <tr class="bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                @if($loop->first)
                                    <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r dark:border-neutral-700 align-top whitespace-nowrap">{{ $jurnal->tanggal->format('d/m/Y') }}</td>
                                    <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r dark:border-neutral-700 align-top">{{ $jurnal->unitWisata->nama ?? '-' }}</td>
                                    <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r dark:border-neutral-700 align-top">{{ $jurnal->keterangan }}</td>
                                @endif
                                <td class="px-4 py-4 border-b dark:border-neutral-700">
                                    {{ $jurnal->kodeAkun->kode ?? '-' }} - {{ $jurnal->kodeAkun->nama ?? 'Akun Tidak Ditemukan' }}
                                </td>
                                <td class="px-4 py-4 text-right border-b dark:border-neutral-700">
                                    {{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-4 text-right border-b dark:border-neutral-700">
                                    {{ $jurnal->kredit > 0 ? number_format($jurnal->kredit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
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
