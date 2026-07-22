<div>
 <div class="flex h-full w-full flex-col gap-6">
 <div class="flex items-center justify-between">
 <h1 class="text-2xl font-semibold text-neutral-900">Ekspor PDF BUMDes</h1>
 <flux:button variant="primary" icon="document-arrow-down" wire:click="exportPdf" wire:loading.attr="disabled">Ekspor PDF</flux:button>
 </div>

 <div class="bg-white p-6 rounded-xl border border-neutral-200 print:hidden">
 <h2 class="text-lg font-semibold mb-4 text-neutral-900">Filter Laporan</h2>
 
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

 <div class="bg-white p-6 rounded-xl border border-neutral-200 flex-1">
 <h2 class="text-lg font-semibold mb-4 text-neutral-900">Pratinjau Jurnal Umum</h2>
 <div class="overflow-x-auto">
 <table class="w-full text-sm text-left text-neutral-600 border border-neutral-200">
 <thead class="text-xs text-neutral-700 uppercase bg-neutral-50">
 <tr>
 <th scope="col" class="px-4 py-3 border-b">Tanggal</th>
 <th scope="col" class="px-4 py-3 border-b">Unit Usaha</th>
 <th scope="col" class="px-4 py-3 border-b">Keterangan</th>
 <th scope="col" class="px-4 py-3 border-b">Akun (COA)</th>
 <th scope="col" class="px-4 py-3 text-right border-b">Debit</th>
 <th scope="col" class="px-4 py-3 text-right border-b">Kredit</th>
 </tr>
 </thead>
 <tbody>
 @forelse($journals as $nomorBukti => $group)
 @foreach($group as $jurnal)
 <tr class="bg-white hover:bg-neutral-50 :bg-neutral-800">
 @if($loop->first)
 <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r align-top whitespace-nowrap">{{ $jurnal->tanggal->format('d/m/Y') }}</td>
 <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r align-top">{{ $jurnal->unitWisata->nama ?? '-' }}</td>
 <td rowspan="{{ $group->count() }}" class="px-4 py-4 border-b border-r align-top">{{ $jurnal->keterangan }}</td>
 @endif
 <td class="px-4 py-4 border-b">
 {{ $jurnal->kodeAkun->kode ?? '-' }} - {{ $jurnal->kodeAkun->nama ?? 'Akun Tidak Ditemukan' }}
 </td>
 <td class="px-4 py-4 text-right border-b">
 {{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}
 </td>
 <td class="px-4 py-4 text-right border-b">
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

