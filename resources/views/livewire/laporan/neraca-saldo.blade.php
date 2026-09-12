<div class="flex h-full w-full flex-col gap-4 sm:gap-6 max-w-full mx-auto pb-10">
    <style>
            .btn-edit-yellow {
                background-color: #f59e0b !important;
                color: #ffffff !important;
                border: 1px solid #f59e0b !important;
            }
            .btn-edit-yellow:hover {
                background-color: #d97706 !important;
                border-color: #d97706 !important;
            }
        </style>
    <x-page-header title="Neraca Saldo" description="Ringkasan saldo akun untuk memastikan keseimbangan debit & kredit.">
        <x-slot:actions>
            @if($this->canPrint)
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full sm:w-auto">
                @if($isEditing)
                    <flux:button variant="danger" wire:click="cancelEditing" class="w-full sm:w-auto shrink-0">Batal</flux:button>
                    <flux:button variant="primary" wire:click="saveAdjustments" class="w-full sm:w-auto shrink-0" wire:loading.attr="disabled" wire:target="saveAdjustments">Simpan Perubahan</flux:button>
                @else
                    <flux:button variant="primary" icon="pencil" wire:click="startEditing" class="w-full sm:w-auto shrink-0 btn-edit-yellow">Edit</flux:button>
                    <flux:button variant="primary" icon="document-arrow-down"
                        wire:click="exportPdf" wire:loading.attr="disabled"
                        class="w-full sm:w-auto shrink-0">
                        Cetak PDF
                    </flux:button>
                @endif
            </div>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white p-4 sm:p-6 rounded-xl border border-brand-100 shadow-sm">
        <h2 class="text-base sm:text-lg font-semibold mb-4 text-zinc-900">Filter Laporan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                    <select wire:model.live="unit_id" class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none focus:outline-none transition-colors">
                        <option value="">Semua Unit</option>
                        @foreach ($this->units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endunless

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Periode</label>
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
        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none focus:outline-none transition-colors cursor-pointer" />
</div>
                </div>
            </div>
        </div>

        @php $data = $this->reportData; @endphp

        <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">
            <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
                <p class="text-sm font-bold text-zinc-900 uppercase">NERACA</p>
                <p class="text-sm text-zinc-600">Per Akhir Bulan: {{ \Carbon\Carbon::parse($this->periode ?: now()->format('Y-m'))->translatedFormat('F Y') }}</p>
            </div>
            
            <div class="flex justify-end px-4 sm:px-6 py-3 {{ $data['totalAktiva'] === $data['totalPasiva'] ? 'bg-emerald-50 border-emerald-100' : 'bg-red-50 border-red-100' }} border-b">
                <div class="flex items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wider {{ $data['totalAktiva'] === $data['totalPasiva'] ? 'text-emerald-600' : 'text-red-600' }}">Status:</p>
                    <p class="text-sm font-bold flex items-center gap-1.5 {{ $data['totalAktiva'] === $data['totalPasiva'] ? 'text-emerald-700' : 'text-red-700' }}">
                        @if($data['totalAktiva'] === $data['totalPasiva'])
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            SEIMBANG
                        @else
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2.m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" /></svg>
                            TIDAK SEIMBANG
                        @endif
                    </p>
                </div>
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
                                @foreach($data['aktivaLancar'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500">{{ $item->kode }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->nama }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->saldo == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        @if($isEditing)
                                            <div class="flex justify-end">
                                                <input type="number" wire:model="editValues.{{ $item->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal text-zinc-900" />
                                            </div>
                                        @else
                                            {{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH AKTIVA LANCAR</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalAktivaLancar'], 0, ',', '.') }}</td>
                                </tr>
                                
                                <!-- Non-current assets -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">1-2000 AKTIVA TIDAK LANCAR</td>
                                </tr>
                                @foreach($data['aktivaTetap'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500">{{ $item->kode }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->nama }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->saldo == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        @if($isEditing)
                                            <div class="flex justify-end">
                                                <input type="number" wire:model="editValues.{{ $item->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal text-zinc-900" />
                                            </div>
                                        @else
                                            {{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH AKTIVA TETAP</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalAktivaTetap'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TOTAL ASSETS pinned to the bottom with margin-top auto to stay aligned --}}
                    <div class="mt-auto border-t-2 border-zinc-400 bg-[#78a2a8] text-zinc-900">
                        <table class="w-full min-w-[300px]">
                            <tr>
                                <td class="px-3 py-4 font-extrabold text-center uppercase text-sm" style="width: 70%;">TOTAL AKTIVA</td>
                                <td class="px-3 py-4 text-right font-extrabold text-sm whitespace-nowrap" style="width: 30%;">{{ number_format($data['totalAktiva'], 0, ',', '.') }}</td>
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
                                @foreach($data['kewajibanPendek'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 pl-6">{{ $item->kode }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->nama }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->saldo == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        @if($isEditing)
                                            <div class="flex justify-end">
                                                <input type="number" wire:model="editValues.{{ $item->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal text-zinc-900" />
                                            </div>
                                        @else
                                            {{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                
                                <tr class="bg-zinc-50/50">
                                    <td class="px-3 py-1.5 font-bold text-xs pl-6" colspan="3">2-2000 KEWAJIBAN JANGKA PANJANG</td>
                                </tr>
                                @foreach($data['kewajibanPanjang'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 pl-6">{{ $item->kode }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800">{{ $item->nama }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->saldo == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        @if($isEditing)
                                            <div class="flex justify-end">
                                                <input type="number" wire:model="editValues.{{ $item->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal text-zinc-900" />
                                            </div>
                                        @else
                                            {{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH KEWAJIBAN</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalKewajiban'], 0, ',', '.') }}</td>
                                </tr>
                                
                                <!-- Equity -->
                                <tr class="bg-zinc-50 border-b border-zinc-200">
                                    <td class="px-3 py-2 font-bold text-xs" colspan="3">3-0000 EKUITAS</td>
                                </tr>
                                @foreach($data['ekuitas'] as $item)
                                <tr class="border-b border-zinc-100 border-dashed hover:bg-zinc-50 transition-colors">
                                    <td class="px-3 py-1.5 w-[15%] min-w-[50px] text-xs font-mono text-zinc-500 pl-6">{{ $item->kode }}</td>
                                    <td class="px-3 py-1.5 w-[55%] font-medium text-zinc-800 {{ $item->nama === 'LABA BERSIH' ? 'uppercase' : '' }}">{{ $item->nama }}</td>
                                    <td class="px-3 py-1.5 w-[30%] text-right font-medium whitespace-nowrap {{ $item->saldo == 0 ? 'text-zinc-400' : 'text-zinc-900' }}">
                                        @if($isEditing)
                                            <div class="flex justify-end">
                                                <input type="number" wire:model="editValues.{{ $item->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal text-zinc-900" />
                                            </div>
                                        @else
                                            {{ $item->saldo == 0 ? '-' : number_format($item->saldo, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                
                                <tr class="bg-zinc-100/50 border-y border-zinc-300">
                                    <td class="px-3 py-2 font-bold text-center text-xs sm:text-sm" colspan="2">JUMLAH EKUITAS</td>
                                    <td class="px-3 py-2 text-right font-bold whitespace-nowrap">{{ number_format($data['totalEkuitas'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    {{-- TOTAL LIABILITIES & EQUITY pinned to the bottom with margin-top auto --}}
                    <div class="mt-auto border-t-2 border-zinc-400 bg-[#78a2a8] text-zinc-900">
                        <table class="w-full min-w-[300px]">
                            <tr>
                                <td class="px-3 py-4 font-extrabold text-center uppercase text-sm" style="width: 70%;">TOTAL PASIVA</td>
                                <td class="px-3 py-4 text-right font-extrabold text-sm whitespace-nowrap" style="width: 30%;">{{ number_format($data['totalPasiva'], 0, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
