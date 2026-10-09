<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-full mx-auto pb-10">
    <x-page-header title="Neraca Saldo" description="Ringkasan saldo akun untuk memastikan keseimbangan debit & kredit." />

    <div class="bg-white p-4 sm:p-6 rounded-xl border border-brand-100 shadow-sm">
        <h2 class="text-base sm:text-lg font-semibold mb-4 text-zinc-900">Filter Laporan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Entitas / Unit Usaha</label>
                    <select wire:model.live.debounce.250ms="unit_id" class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none focus:outline-none transition-colors font-medium">
                        <option value="bumdes">BUMDes</option>
                        <option value="all">Semua Unit Usaha</option>
                        <optgroup label="Per Unit Usaha">
                            @foreach ($this->units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            @endunless

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Periode</label>
                    <div wire:ignore x-data="{ val: $wire.entangle('period').live }">
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
        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none focus:outline-none transition-colors cursor-pointer" />
</div>
                </div>
            </div>
        </div>

        @php $data = $this->reportData; @endphp

        <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
                <p class="text-sm font-bold text-zinc-900 uppercase">{{ $entityName }}</p>
                <p class="text-sm font-bold text-zinc-900 uppercase">NERACA SALDO</p>
            </div>

            <div class="flex flex-col md:flex-row w-full text-sm text-zinc-700">
                {{-- ASSETS SIDE --}}
                <div class="w-full md:w-1/2 md:border-r border-zinc-300 flex flex-col">
                    <div class="bg-zinc-200/60 py-2 text-center font-bold border-b border-zinc-300 uppercase tracking-widest text-zinc-900">Aktiva</div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full min-w-[300px]">
                            <tbody>
                                <!-- Current assets -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">1-1000 AKTIVA LANCAR</td>
                                </tr>
                                @foreach($data['currentAssets'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 whitespace-nowrap">{{ $item->code }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->name }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->balance == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        {{ $item->balance == 0 ? '-' : number_format($item->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH AKTIVA LANCAR</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalCurrentAssets'], 0, ',', '.') }}</td>
                                </tr>
                                
                                <!-- Non-current assets -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">1-2000 AKTIVA TIDAK LANCAR</td>
                                </tr>
                                @foreach($data['fixedAssets'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 whitespace-nowrap">{{ $item->code }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->name }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->balance == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                            {{ $item->balance == 0 ? '-' : number_format($item->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH AKTIVA TETAP</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalFixedAssets'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TOTAL ASSETS pinned to the bottom with margin-top auto to stay aligned --}}
                    <div class="mt-auto border-t-2 border-zinc-400 bg-[#78a2a8] text-zinc-900">
                        <table class="w-full min-w-[300px]">
                            <tr>
                                <td class="px-3 py-4 font-extrabold text-center uppercase text-sm" style="width: 70%;">TOTAL AKTIVA</td>
                                <td class="px-3 py-4 text-right font-extrabold text-sm whitespace-nowrap" style="width: 30%;">{{ number_format($data['totalAssets'], 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- LIABILITIES & EQUITY SIDE --}}
                <div class="w-full md:w-1/2 flex flex-col border-t-4 md:border-t-0 border-zinc-300 md:border-transparent mt-6 md:mt-0">
                    <div class="bg-zinc-200/60 py-2 text-center font-bold border-b border-zinc-300 uppercase tracking-widest text-zinc-900">Pasiva</div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full min-w-[300px]">
                            <tbody>
                                <!-- Liabilities -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">2-0000 KEWAJIBAN</td>
                                </tr>
                                <tr class="bg-zinc-50/50">
                                    <td class="px-3 py-1.5 font-bold text-xs pl-6" colspan="3">2-1000 KEWAJIBAN JANGKA PENDEK</td>
                                </tr>
                                @foreach($data['currentLiabilities'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 whitespace-nowrap pl-6">{{ $item->code }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->name }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->balance == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        {{ $item->balance == 0 ? '-' : number_format($item->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                                
                                <tr class="bg-zinc-50/50">
                                    <td class="px-3 py-1.5 font-bold text-xs pl-6" colspan="3">2-2000 KEWAJIBAN JANGKA PANJANG</td>
                                </tr>
                                @foreach($data['longTermLiabilities'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 whitespace-nowrap pl-6">{{ $item->code }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->name }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->balance == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        {{ $item->balance == 0 ? '-' : number_format($item->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach

                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH KEWAJIBAN</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalLiabilities'], 0, ',', '.') }}</td>
                                </tr>
                                
                                <!-- Equity -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">3-0000 EKUITAS</td>
                                </tr>
                                @foreach($data['equity'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 whitespace-nowrap pl-6">{{ $item->code }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800 {{ $item->name === 'LABA BERSIH' ? 'uppercase' : '' }}">{{ $item->name }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->balance == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        {{ $item->balance == 0 ? '-' : number_format($item->balance, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                                
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH EKUITAS</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalEquity'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TOTAL LIABILITIES & EQUITY pinned to the bottom with margin-top auto --}}
                    <div class="mt-auto border-t-2 border-zinc-400 bg-[#78a2a8] text-zinc-900">
                        <table class="w-full min-w-[300px]">
                            <tr>
                                <td class="px-3 py-4 font-extrabold text-center uppercase text-sm" style="width: 70%;">TOTAL PASIVA</td>
                                <td class="px-3 py-4 text-right font-extrabold text-sm whitespace-nowrap" style="width: 30%;">{{ number_format($data['totalLiabilitiesEquity'], 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($this->canPrint)
            <x-report-actions :preview-url="route('reports.preview', ['report' => 'neraca-saldo', 'unit' => $unit_id, 'period' => $period])" sticky />
        @endif
    </div>
