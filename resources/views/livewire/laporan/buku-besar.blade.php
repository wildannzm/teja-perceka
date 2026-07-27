<div>
<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-5xl mx-auto pb-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl sm:text-2xl font-semibold text-zinc-900">Laporan Buku Besar</h1>
            <p class="text-sm text-zinc-500">Rincian mutasi transaksi per kode akun.</p>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $this->isKepalaUnit ? '' : 'md:grid-cols-3' }} gap-3 sm:gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Kode Akun</label>
                    <select wire:model.live="kode_akun_id" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">-- Pilih Akun --</option>
                        @foreach ($this->akuns as $akun)
                            <option value="{{ $akun->id }}">{{ $akun->kode }} - {{ $akun->nama }}</option>
                        @endforeach
                    </select>
                </div>

                @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                    <select wire:model.live="unit_id" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">Semua Unit (Konsolidasi)</option>
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endunless

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Bulan</label>
                    <input type="month" wire:model.live="periode" class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                </div>
            </div>
        </div>

        @php $data = $this->reportData; @endphp

        @if($data['selectedAkun'])
        <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
                <p class="text-sm font-bold text-zinc-900 uppercase">BUKU BESAR - {{ $data['selectedAkun']->nama }}</p>
                <p class="text-sm text-zinc-600">Kode Akun: {{ $data['selectedAkun']->kode }} | Saldo Normal: <span class="uppercase">{{ $data['normalBalance'] }}</span></p>
            </div>
            
            <div class="flex justify-end px-4 sm:px-6 py-3 bg-zinc-50 border-b border-zinc-200">
                <div class="text-right">
                    <p class="text-xs font-bold text-zinc-500 uppercase">SALDO AWAL</p>
                    <p class="text-sm font-bold text-brand-700">Rp {{ number_format($data['saldoAwal'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="overflow-hidden md:overflow-x-auto">
                <table class="w-full text-sm text-left text-zinc-600">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Tanggal</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Keterangan</th>
                            <th scope="col" class="px-5 py-4 whitespace-nowrap">Nomor Bukti</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap">Debit</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap">Kredit</th>
                            <th scope="col" class="px-5 py-4 text-right whitespace-nowrap bg-brand-50/50">Saldo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @php
                            $runningBalance = $data['saldoAwal'];
                        @endphp
                        
                        @forelse($data['transactions'] as $jurnal)
                            @php
                                if($data['normalBalance'] === 'debit') {
                                    $runningBalance += $jurnal->debet - $jurnal->kredit;
                                } else {
                                    $runningBalance += $jurnal->kredit - $jurnal->debet;
                                }
                            @endphp
                            <tr class="bg-white hover:bg-zinc-50 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap font-medium text-zinc-900">
                                    {{ $jurnal->tanggal->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-4 min-w-[200px]">
                                    {{ $jurnal->keterangan }}
                                    @if($jurnal->unitWisata)
                                    <div class="text-xs text-brand-600 mt-1 font-medium">{{ $jurnal->unitWisata->nama }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-mono text-xs">
                                    {{ $jurnal->nomor_bukti }}
                                </td>
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $jurnal->debet > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                    {{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-medium whitespace-nowrap {{ $jurnal->kredit > 0 ? 'text-zinc-900' : 'text-zinc-400' }}">
                                    {{ $jurnal->kredit > 0 ? number_format($jurnal->kredit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-bold whitespace-nowrap bg-brand-50/30 text-brand-700">
                                    {{ number_format($runningBalance, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-zinc-500">
                                    <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Belum ada data transaksi di bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($data['transactions']) > 0)
                    <tfoot class="bg-zinc-50 border-t-2 border-zinc-200">
                        <tr>
                            <td colspan="3" class="px-5 py-4 text-right font-bold text-zinc-900">Mutasi Bulan Ini</td>
                            <td class="px-5 py-4 text-right font-bold text-zinc-900">Rp {{ number_format($data['totalDebit'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-bold text-zinc-900">Rp {{ number_format($data['totalKredit'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-extrabold text-brand-700 bg-brand-50/50">Rp {{ number_format($runningBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @else
        <div class="bg-white p-8 rounded-2xl border border-zinc-200 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="size-16 bg-brand-50 rounded-full flex items-center justify-center mb-4">
                <svg class="size-8 text-brand-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900">Pilih Kode Akun</h3>
            <p class="text-sm text-zinc-500 mt-1 max-w-sm">Silakan pilih Kode Akun pada filter di atas untuk melihat rincian Buku Besar.</p>
        </div>
        @endif
    </div>
</div>
