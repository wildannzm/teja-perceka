<div class="flex flex-col gap-5 max-w-5xl mx-auto w-full pb-36">

    {{-- Error Notification --}}
    @if ($errors->has('pdf'))
        <div class="p-4 text-sm text-red-800 bg-red-100 rounded-xl dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800 flex items-start gap-3 shadow-sm" role="alert">
            <svg class="size-5 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium leading-relaxed">{{ $errors->first('pdf') }}</span>
        </div>
    @endif

    {{-- Mode Selector (Segmented Control) --}}
    <div class="bg-zinc-100 dark:bg-zinc-900 p-1.5 rounded-2xl flex items-center shadow-sm border border-zinc-200 dark:border-zinc-800 overflow-x-auto">
        @if (! $isMingguanOnly)
            <button
                wire:click="$set('mode', 'harian')"
                class="flex-1 min-w-[80px] min-h-[44px] text-sm font-semibold rounded-xl transition-all duration-150 {{ $mode === 'harian' ? 'bg-white dark:bg-zinc-800 text-brand-900 dark:text-brand-300 shadow shadow-black/5' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
            >
                Harian
            </button>
        @endif
        <button
            wire:click="$set('mode', 'mingguan')"
            class="flex-1 min-w-[80px] min-h-[44px] text-sm font-semibold rounded-xl transition-all duration-150 {{ $mode === 'mingguan' ? 'bg-white dark:bg-zinc-800 text-brand-900 dark:text-brand-300 shadow shadow-black/5' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Mingguan
        </button>
        <button
            wire:click="$set('mode', 'bulanan')"
            class="flex-1 min-w-[80px] min-h-[44px] text-sm font-semibold rounded-xl transition-all duration-150 {{ $mode === 'bulanan' ? 'bg-white dark:bg-zinc-800 text-brand-900 dark:text-brand-300 shadow shadow-black/5' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Bulanan
        </button>
        <button
            wire:click="$set('mode', 'tahunan')"
            class="flex-1 min-w-[80px] min-h-[44px] text-sm font-semibold rounded-xl transition-all duration-150 {{ $mode === 'tahunan' ? 'bg-white dark:bg-zinc-800 text-brand-900 dark:text-brand-300 shadow shadow-black/5' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}"
        >
            Tahunan
        </button>
    </div>

    {{-- Navigator Periode & Date Picker --}}
    <div class="flex items-center justify-between bg-white dark:bg-zinc-900 rounded-2xl p-2 shadow-sm border border-brand-100 dark:border-zinc-800 flex-wrap gap-2">
        <button
            wire:click="previousPeriod"
            class="p-3 min-w-[52px] min-h-[52px] text-brand-700 dark:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-950/50 rounded-xl transition-colors active:scale-95 flex items-center justify-center shrink-0"
            aria-label="Periode sebelumnya"
        >
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <div class="flex-1 flex flex-col items-center px-2 min-w-[150px]">
            <div class="font-bold text-lg text-brand-900 dark:text-zinc-100 tracking-tight leading-tight text-center" wire:loading.class="opacity-50" wire:target="previousPeriod,nextPeriod,mode,currentDate">
                {{ $this->periodeLabel }}
            </div>
            
            {{-- Date Picker Tersembunyi tapi interaktif --}}
            <div class="mt-2 relative">
                <input 
                    type="date" 
                    wire:model.live="currentDate" 
                    class="block w-full text-sm rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 transition-colors cursor-pointer"
                >
            </div>
        </div>

        <button
            wire:click="nextPeriod"
            class="p-3 min-w-[52px] min-h-[52px] text-brand-700 dark:text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-950/50 rounded-xl transition-colors active:scale-95 flex items-center justify-center shrink-0"
            aria-label="Periode berikutnya"
        >
            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>

    {{-- Info Akun --}}
    @if ($this->transactions->isNotEmpty() && $this->transactions->first()->kodeAkun)
        <div class="bg-brand-50/50 dark:bg-brand-900/10 px-5 py-3 rounded-2xl border border-brand-100 dark:border-brand-900 flex justify-between items-center" wire:loading.class="opacity-60" wire:target="previousPeriod,nextPeriod,mode,currentDate">
            <div>
                <div class="text-[11px] text-brand-600 dark:text-brand-400 font-semibold uppercase tracking-wider mb-0.5">Nama Akun</div>
                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->transactions->first()->kodeAkun->nama }}</div>
            </div>
            <div class="text-right">
                <div class="text-[11px] text-brand-600 dark:text-brand-400 font-semibold uppercase tracking-wider mb-0.5">Kode Akun</div>
                <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100 font-mono">{{ $this->transactions->first()->kodeAkun->kode }}</div>
            </div>
        </div>
    @endif

    {{-- Tabel Buku Kas --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-brand-100 dark:border-zinc-800 overflow-hidden relative" wire:loading.class="opacity-60" wire:target="previousPeriod,nextPeriod,mode,currentDate">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-brand-50/80 dark:bg-brand-950/30 text-brand-900 dark:text-brand-300 border-b border-brand-100 dark:border-brand-900">
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Tanggal</th>
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs">Bukti Transaksi</th>
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs w-full min-w-[200px]">Keterangan</th>
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-right">Debet</th>
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-right">Kredit</th>
                        <th class="py-4 px-4 font-semibold uppercase tracking-wider text-xs text-right bg-brand-50/50 dark:bg-brand-900/20">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
                    @forelse ($this->transactions as $trx)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="py-3 px-4">{{ $trx->tanggal->format('d/m/Y') }}</td>
                            <td class="py-3 px-4 font-mono text-xs text-zinc-500 dark:text-zinc-400">{{ $trx->nomor_bukti }}</td>
                            <td class="py-3 px-4 text-wrap leading-relaxed">{{ $trx->keterangan }}</td>
                            <td class="py-3 px-4 text-right font-medium text-brand-700 dark:text-brand-400">
                                {{ $trx->debet > 0 ? number_format($trx->debet, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-red-600 dark:text-red-400">
                                {{ $trx->kredit > 0 ? number_format($trx->kredit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-zinc-900 dark:text-zinc-100 bg-brand-50/20 dark:bg-brand-900/10">
                                {{ number_format($trx->saldo_berjalan, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center text-zinc-500 dark:text-zinc-400">
                                <svg class="size-12 mx-auto mb-3 text-zinc-300 dark:text-zinc-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Belum ada catatan buku kas untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Sticky Footer: Grand Total + Cetak PDF --}}
    <div class="fixed bottom-0 left-0 right-0 z-20 sm:relative sm:bottom-auto sm:left-auto sm:right-auto sm:z-auto">
        <div class="bg-brand-900 dark:bg-brand-950 sm:rounded-2xl px-5 pt-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] sm:pb-5 shadow-[0_-8px_32px_-8px_rgba(0,0,0,0.4)] sm:shadow-xl border-t sm:border border-brand-800">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <div class="text-brand-300 dark:text-brand-500 text-xs font-medium uppercase tracking-wider mb-0.5">Saldo Akhir Periode</div>
                    <div class="text-brand-100 text-sm">{{ $this->periodeLabel }}</div>
                </div>
                <div class="text-2xl font-bold text-white tracking-tight">
                    Rp {{ number_format($this->grandTotal, 0, ',', '.') }}
                </div>
            </div>

            <button
                wire:click="exportPdf"
                wire:loading.attr="disabled"
                wire:target="exportPdf"
                class="w-full min-h-[52px] rounded-xl text-base font-semibold bg-white text-brand-950 hover:bg-brand-50 active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 cursor-pointer disabled:opacity-70 disabled:cursor-wait disabled:active:scale-100 shadow-lg"
            >
                {{-- Icon & text saat idle --}}
                <span wire:loading.remove wire:target="exportPdf" class="flex items-center gap-2.5">
                    <svg class="size-5 text-brand-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Cetak / Unduh PDF
                </span>
                {{-- Loading state --}}
                <span wire:loading wire:target="exportPdf" class="flex items-center gap-2.5">
                    <svg class="animate-spin size-5 text-brand-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyiapkan PDF...
                </span>
            </button>
        </div>
    </div>

</div>
