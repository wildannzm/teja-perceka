<div>
<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-5xl mx-auto pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl sm:text-2xl font-semibold text-zinc-900">Neraca Saldo</h1>
            <p class="text-sm text-zinc-500">Ringkasan saldo akun untuk memastikan keseimbangan debit & kredit.</p>
        </div>
        @if($this->canPrint)
            <flux:button variant="primary" icon="document-arrow-down"
                wire:click="exportPdf" wire:loading.attr="disabled"
                class="w-full sm:w-auto shrink-0">
                Cetak PDF
            </flux:button>
        @endif
    </div>

    <div class="bg-white p-4 sm:p-6 rounded-xl border border-brand-100 shadow-sm">
        <h2 class="text-base sm:text-lg font-semibold mb-4 text-zinc-900">Filter Laporan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                    <select wire:model.live="unit_id" class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">Semua Unit (Konsolidasi)</option>
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endunless

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Periode</label>
                    <input type="month" wire:model.live="periode" class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                </div>
            </div>
        </div>

        @php $data = $this->reportData; @endphp

        <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
                <p class="text-sm font-bold text-zinc-900 uppercase">NERACA SALDO</p>
                <p class="text-sm text-zinc-600">Per Akhir Bulan: {{ \Carbon\Carbon::parse($this->periode ?: now()->format('Y-m'))->translatedFormat('F Y') }}</p>
            </div>
            
            <div class="flex justify-end px-4 sm:px-6 py-3 {{ $data['totalDebit'] === $data['totalKredit'] ? 'bg-emerald-50 border-emerald-100' : 'bg-red-50 border-red-100' }} border-b">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $data['totalDebit'] === $data['totalKredit'] ? 'text-emerald-600' : 'text-red-600' }}">Status:</p>
                    <p class="text-sm font-bold flex items-center gap-1.5 {{ $data['totalDebit'] === $data['totalKredit'] ? 'text-emerald-700' : 'text-red-700' }}">
                        @if($data['totalDebit'] === $data['totalKredit'])
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            SEIMBANG
                        @else
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>
                            TIDAK SEIMBANG
                        @endif
                    </p>
                </div>
            </div>

            <div class="overflow-hidden md:overflow-x-auto">
                <table class="w-full text-sm text-left text-zinc-600">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Kode Akun</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Nama Akun</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap w-1/4">Debit (Rp)</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap w-1/4">Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse($data['neracaData'] as $row)
                            <tr class="bg-white hover:bg-zinc-50 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-xs text-brand-600 font-semibold">
                                    {{ $row->kode }}
                                </td>
                                <td class="px-5 py-4 min-w-[200px] font-medium text-zinc-900">
                                    {{ $row->nama }}
                                </td>
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $row->debit > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                    {{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $row->kredit > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                    {{ $row->kredit > 0 ? number_format($row->kredit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-zinc-500">
                                    <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Tidak ada data saldo akun hingga periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($data['neracaData']) > 0)
                    <tfoot class="bg-zinc-50 border-t-2 border-zinc-200">
                        <tr>
                            <td colspan="2" class="px-5 py-4 text-right font-bold text-zinc-900">TOTAL</td>
                            <td class="px-5 py-4 text-right font-bold {{ $data['totalDebit'] === $data['totalKredit'] ? 'text-emerald-600' : 'text-zinc-900' }}">
                                Rp {{ number_format($data['totalDebit'], 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right font-bold {{ $data['totalDebit'] === $data['totalKredit'] ? 'text-emerald-600' : 'text-zinc-900' }}">
                                Rp {{ number_format($data['totalKredit'], 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
