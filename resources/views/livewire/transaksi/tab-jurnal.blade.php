{{-- Tab Jurnal Umum --}}
<div class="flex flex-col gap-5 min-w-0 pb-10">

    @if ($errors->has('pdf'))
        <div class="p-4 text-sm text-red-800 bg-red-100 rounded-xl border border-red-200 flex items-start gap-3 shadow-sm" role="alert">
            <svg class="w-5 h-5 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium leading-relaxed">{{ $errors->first('pdf') }}</span>
        </div>
    @endif

    {{-- Jurnal List --}}
    @php $journals = $this->transactions; @endphp

    @if($journals->isEmpty())
        <div class="bg-white rounded-2xl border-2 border-dashed border-zinc-200 p-8 sm:p-12 text-center flex flex-col items-center justify-center min-h-[300px]">
            <div class="bg-zinc-100 text-zinc-400 p-4 rounded-full mb-4 inline-block">
                <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-zinc-900 mb-2">Belum Ada Jurnal</h3>
            <p class="text-zinc-500 text-sm max-w-md mx-auto">Belum ada catatan jurnal umum untuk periode ini.</p>
        </div>
    @else
        {{-- Desktop: Table view (hidden on mobile) --}}
        <div class="hidden sm:block bg-white rounded-2xl shadow-sm border border-brand-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-brand-50/80 text-brand-900 border-b border-brand-100">
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Bukti</th>
                            @if(is_null($unitId))
                                <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Unit</th>
                            @endif
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs w-full min-w-[200px]">Keterangan</th>
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Kode Akun</th>
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-right">Debit</th>
                            <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-right">Kredit</th>
                            @if($this->canDelete)
                                <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700">
                        @foreach($journals as $nomorBukti => $group)
                            @foreach($group as $jurnal)
                                <tr class="hover:bg-zinc-50 transition-colors">
                                    @if($loop->first)
                                        <td rowspan="{{ $group->count() }}" class="py-3 px-4 align-top border-r border-zinc-100">{{ $jurnal->tanggal->translatedFormat('d F Y') }}</td>
                                        <td rowspan="{{ $group->count() }}" class="py-3 px-4 font-mono text-xs text-zinc-500 align-top border-r border-zinc-100">{{ $jurnal->nomor_bukti }}</td>
                                        @if(is_null($unitId))
                                            <td rowspan="{{ $group->count() }}" class="py-3 px-4 align-top border-r border-zinc-100">{{ $jurnal->unitWisata?->nama ?? '-' }}</td>
                                        @endif
                                        <td rowspan="{{ $group->count() }}" class="py-3 px-4 text-wrap leading-relaxed align-top border-r border-zinc-100">{{ $jurnal->keterangan }}</td>
                                    @endif
                                    <td class="py-3 px-4 font-mono text-xs">{{ $jurnal->kodeAkun?->kode ?? '-' }} - {{ $jurnal->kodeAkun?->nama ?? '?' }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-brand-700">{{ $jurnal->debet > 0 ? number_format($jurnal->debet, 0, ',', '.') : '-' }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-red-600">{{ $jurnal->kredit > 0 ? number_format($jurnal->kredit, 0, ',', '.') : '-' }}</td>
                                    @if($this->canDelete)
                                        @if($loop->first)
                                            <td rowspan="{{ $group->count() }}" class="py-3 px-4 align-middle text-center border-l border-zinc-100">
                                                <flux:button wire:click="confirmDelete({{ $jurnal->id }})"
                                                    variant="danger" size="sm" icon="trash">Hapus</flux:button>
                                            </td>
                                        @endif
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile: Card per transaksi (visible only on mobile) --}}
        <div class="flex flex-col gap-3 sm:hidden">
            @foreach($journals as $nomorBukti => $group)
                @php $firstJurnal = $group->first(); @endphp
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                    {{-- Card Header --}}
                    <div class="bg-brand-50/60 border-b border-brand-100 px-4 py-3 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs font-mono text-zinc-500">{{ $firstJurnal->nomor_bukti }}</p>
                            <p class="text-sm font-semibold text-zinc-800 mt-0.5">{{ $firstJurnal->tanggal->translatedFormat('d F Y') }}</p>
                            @if(is_null($unitId))
                                <p class="text-xs text-zinc-500 mt-0.5">{{ $firstJurnal->unitWisata?->nama ?? '-' }}</p>
                            @endif
                        </div>
                        @if($this->canDelete)
                            <flux:button wire:click="confirmDelete({{ $firstJurnal->id }})"
                                variant="danger" size="xs" icon="trash" class="shrink-0 mt-0.5" />
                        @endif
                    </div>

                    {{-- Keterangan --}}
                    <div class="px-4 py-3 border-b border-zinc-100">
                        <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider mb-0.5">Keterangan</p>
                        <p class="text-sm text-zinc-800 leading-relaxed">{{ $firstJurnal->keterangan }}</p>
                    </div>

                    {{-- Baris Jurnal (per entry Debit/Kredit) --}}
                    <div class="divide-y divide-zinc-100">
                        @foreach($group as $jurnal)
                            <div class="px-4 py-3 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-mono text-zinc-500 truncate">{{ $jurnal->kodeAkun?->kode ?? '-' }}</p>
                                    <p class="text-sm text-zinc-700 font-medium truncate">{{ $jurnal->kodeAkun?->nama ?? '?' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    @if($jurnal->debet > 0)
                                        <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Debit</p>
                                        <p class="text-sm font-bold text-brand-700">Rp {{ number_format($jurnal->debet, 0, ',', '.') }}</p>
                                    @else
                                        <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Kredit</p>
                                        <p class="text-sm font-bold text-red-600">Rp {{ number_format($jurnal->kredit, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Spacer for mobile footer to prevent overlap --}}
    <div style="height: 350px; flex-shrink: 0;" class="w-full block sm:hidden"></div>

    {{-- Footer: Grand Total + Cetak PDF --}}
    <div class="fixed bottom-0 left-0 right-0 z-20 sm:relative sm:bottom-auto sm:left-auto sm:right-auto sm:z-auto sm:mt-2">
        <div class="bg-white sm:rounded-2xl p-5 sm:p-6 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-6 shadow-[0_-8px_32px_-8px_rgba(0,0,0,0.1)] sm:shadow-md border-t sm:border border-brand-100 flex flex-col gap-4">
            
            <div class="flex flex-col gap-2 px-1 sm:px-2 mb-1">
                <div class="border-b border-brand-100 pb-2 mb-1 flex justify-between items-center">
                    <span class="text-zinc-500 font-semibold text-xs uppercase tracking-wider">Total Jurnal Umum</span>
                    <span class="text-zinc-600 text-xs font-medium">{{ $this->periodeLabel }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-zinc-600 font-semibold text-sm sm:text-base">Total Debit</span>
                    <span class="text-xl sm:text-2xl font-extrabold text-brand-600 tracking-tight whitespace-nowrap">Rp {{ number_format($this->totalDebet, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-zinc-600 font-semibold text-sm sm:text-base">Total Kredit</span>
                    <span class="text-xl sm:text-2xl font-extrabold text-red-600 tracking-tight whitespace-nowrap">Rp {{ number_format($this->totalKredit, 0, ',', '.') }}</span>
                </div>
            </div>

            @if($this->canExportPdf)
                <button wire:click="exportPdf" wire:loading.attr="disabled" wire:target="exportPdf"
                    class="w-full min-h-[56px] rounded-2xl text-base font-semibold shadow-md border border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-4 focus:ring-brand-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-70 disabled:cursor-wait">
                    <span wire:loading.remove wire:target="exportPdf" class="flex items-center gap-2.5">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Cetak / Unduh PDF
                    </span>
                    <span wire:loading wire:target="exportPdf" class="flex items-center gap-2.5">
                        <svg class="animate-spin size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyiapkan PDF...
                    </span>
                </button>
            @else
                <div class="w-full min-h-[56px] rounded-2xl text-base font-semibold shadow-md border border-brand-200 bg-brand-50 text-brand-600/60 flex items-center justify-center gap-2.5 cursor-not-allowed select-none">
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    Cetak PDF (Bulanan / Tahunan saja)
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
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
