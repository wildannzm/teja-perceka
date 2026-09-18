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
    <x-page-header title="Laporan Laba Rugi" description="Rekap pendapatan dan biaya operasional per periode.">
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

    {{-- Filter --}}
    <div class="bg-white p-4 sm:p-6 rounded-xl border border-brand-100 shadow-sm">
        <h2 class="text-base sm:text-lg font-semibold mb-4 text-zinc-900">Filter Laporan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $this->isKepalaUnit ? '' : 'md:grid-cols-3' }} gap-3 sm:gap-4">

            {{-- Unit selector (non unit-head roles only) --}}
            @unless($this->isKepalaUnit)
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-zinc-700">Unit Usaha</label>
                    <select wire:model.live="unit_id"
                        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                        <option value="">Semua Unit</option>
                        @foreach($this->units as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            {{-- Mode --}}
            <div class="flex flex-col gap-1.5">
                <label class="text-sm font-medium text-zinc-700">Periode</label>
                <select wire:model.live="mode"
                    class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                    <option value="monthly">Bulanan</option>
                    <option value="semester">Semester</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>

            {{-- Period input --}}
            <div class="flex flex-col gap-1.5">
                @if($mode === 'monthly')
                    <label class="text-sm font-medium text-zinc-700">Bulan</label>
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
        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors cursor-pointer" />
</div>
                @elseif($mode === 'semester')
                    <label class="text-sm font-medium text-zinc-700">Semester &amp; Tahun</label>
                    <div class="flex gap-2">
                        <select wire:model.live="semester"
                            class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                            <option value="1">Sem 1 (Jan-Jun)</option>
                            <option value="2">Sem 2 (Jul-Des)</option>
                        </select>
                        <input type="number" wire:model.live.debounce.500ms="semesterYear" min="2020" max="2099" placeholder="{{ date('Y') }}"
                            class="block w-24 max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                    </div>
                @else
                    <label class="text-sm font-medium text-zinc-700">Tahun</label>
                    <input type="number" wire:model.live.debounce.500ms="period" min="2020" max="2099"
                        placeholder="{{ date('Y') }}"
                        class="block w-full max-w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:ring-0 focus:outline-none transition-colors">
                @endif
            </div>
        </div>
    </div>

    {{-- Report --}}
    @php
        $data = $this->reportData;
        $revenueRows = $data['revenueRows'];
        $totalRevenue = $data['totalRevenue'];
        $cogsRows = $data['cogsRows'];
        $totalHpp = $data['totalHpp'];
        $grossProfit = $data['grossProfit'];
        $expenseRows = $data['expenseRows'];
        $totalExpenses = $data['totalExpenses'];
        $otherRevenueRows = $data['otherRevenueRows'];
        $totalOtherRevenue = $data['totalOtherRevenue'];
        $otherExpenseRows = $data['otherExpenseRows'];
        $totalOtherExpenses = $data['totalOtherExpenses'];
        $netIncome = $data['netIncome'];
    @endphp

    <div class="bg-white rounded-xl border border-brand-100 shadow-sm overflow-hidden mb-6 pb-6">

        {{-- Report sub-header --}}
        <div class="px-4 sm:px-6 py-4 border-b border-zinc-100 text-center">
            <p class="text-sm font-bold text-zinc-900 uppercase">{{ $entityName }}</p>
            <p class="text-sm font-bold text-zinc-900">LABA RUGI</p>
            <p class="text-sm text-zinc-600">{{ $printDate }}</p>
        </div>

        <div class="overflow-hidden md:overflow-x-auto">
            <table class="w-full text-sm text-left text-zinc-700 whitespace-normal md:whitespace-nowrap block md:table">
                <thead class="hidden md:table-header-group text-xs font-bold text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 w-32">KODE AKUN</th>
                        <th class="px-4 sm:px-6 py-3">NAMA AKUN</th>
                        <th class="px-4 sm:px-6 py-3 text-right w-40">JUMLAH</th>
                        <th class="px-4 sm:px-6 py-3 text-right w-40">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="block md:table-row-group">

                    {{-- ── OPERATING REVENUE ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            PENDAPATAN USAHA
                        </td>
                    </tr>
                    @foreach($revenueRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->code }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->name }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                @if($isEditing)
                                    <div class="flex justify-end">
                                        <input type="number" wire:model="editValues.{{ $row->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal" />
                                    </div>
                                @else
                                    {{ $row->amount != 0 ? 'Rp '.number_format($row->amount, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH PENDAPATAN
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH PENDAPATAN
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalRevenue != 0 ? 'Rp '.number_format($totalRevenue, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── COST OF GOODS SOLD ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            HARGA POKOK PENJUALAN
                        </td>
                    </tr>
                    @foreach($cogsRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->code }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->name }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                @if($isEditing)
                                    <div class="flex justify-end">
                                        <input type="number" wire:model="editValues.{{ $row->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal" />
                                    </div>
                                @else
                                    {{ $row->amount != 0 ? 'Rp '.number_format($row->amount, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH HPP
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH HPP
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalHpp != 0 ? 'Rp '.number_format($totalHpp, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                    <tr class="flex justify-between items-center md:table-row bg-brand-50/50 border-t border-brand-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-brand-900 uppercase text-xs tracking-wide text-right">
                            LABA KOTOR
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-brand-900 uppercase text-xs tracking-wide">
                            LABA KOTOR
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-brand-900">
                            {{ $grossProfit != 0 ? 'Rp '.number_format($grossProfit, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── OPERATING EXPENSES ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            BIAYA USAHA
                        </td>
                    </tr>
                    @foreach($expenseRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->code }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->name }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-red-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                @if($isEditing)
                                    <div class="flex justify-end">
                                        <input type="number" wire:model="editValues.{{ $row->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal" />
                                    </div>
                                @else
                                    {{ $row->amount != 0 ? 'Rp '.number_format($row->amount, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    <tr class="flex justify-between items-center md:table-row bg-zinc-50/70 border-t-2 border-zinc-200 px-4 py-3 md:p-0">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide text-right">
                            JUMLAH BIAYA USAHA
                        </td>
                        <td class="block md:hidden md:px-6 md:py-3 font-bold text-zinc-900 uppercase text-xs tracking-wide">
                            JUMLAH BIAYA USAHA
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-3 text-right font-bold text-zinc-900">
                            {{ $totalExpenses != 0 ? 'Rp '.number_format($totalExpenses, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                    {{-- ── OTHER INCOME & EXPENSES ── --}}
                    <tr class="block md:table-row bg-zinc-100/50 border-b md:border-none border-zinc-200 mt-2 md:mt-0">
                        <td colspan="4" class="block md:table-cell px-4 sm:px-6 py-3 text-xs font-bold text-zinc-800 uppercase tracking-wider">
                            PENDAPATAN & BIAYA LAIN-LAIN
                        </td>
                    </tr>
                    @foreach($otherRevenueRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->code }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->name }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-brand-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                @if($isEditing)
                                    <div class="flex justify-end">
                                        <input type="number" wire:model="editValues.{{ $row->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal" />
                                    </div>
                                @else
                                    {{ $row->amount != 0 ? 'Rp '.number_format($row->amount, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach
                    @foreach($otherExpenseRows as $row)
                        <tr class="flex flex-col md:table-row border-b border-zinc-100 hover:bg-zinc-50 transition-colors px-4 py-3 md:p-0">
                            <td class="block md:table-cell md:px-6 md:py-3 font-mono text-xs text-zinc-500 order-2 md:order-none">{{ $row->code }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-base md:text-sm font-semibold md:font-normal text-zinc-900 order-1 md:order-none mb-1 md:mb-0">{{ $row->name }}</td>
                            <td class="block md:table-cell md:px-6 md:py-3 text-left md:text-right font-bold md:font-medium text-red-700 md:text-zinc-800 order-3 md:order-none mt-2 md:mt-0 text-base md:text-sm">
                                @if($isEditing)
                                    <div class="flex justify-end">
                                        <input type="number" wire:model="editValues.{{ $row->id }}" class="w-32 rounded-lg border-2 border-brand-500 text-sm px-2 py-1 focus:ring-0 focus:outline-none text-right font-normal" />
                                    </div>
                                @else
                                    {{ $row->amount != 0 ? 'Rp '.number_format($row->amount, 0, ',', '.') : '-' }}
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-3"></td>
                        </tr>
                    @endforeach

                    {{-- ── NET PROFIT ── --}}
                    <tr class="flex justify-between items-center md:table-row {{ $netIncome >= 0 ? 'bg-emerald-600' : 'bg-red-600' }} px-4 py-4 md:p-0 mt-4 md:mt-0 rounded-b-xl md:rounded-none">
                        <td colspan="3" class="hidden md:table-cell md:px-6 md:py-4 font-extrabold text-white uppercase tracking-wide text-sm md:text-base text-right">
                            LABA BERSIH
                        </td>
                        <td class="block md:hidden md:px-6 md:py-4 font-extrabold text-white uppercase tracking-wide text-sm md:text-base">
                            LABA BERSIH
                        </td>
                        <td class="block md:table-cell md:px-6 md:py-4 text-right font-extrabold text-white text-lg md:text-base">
                            {{ $netIncome != 0 ? 'Rp '.number_format($netIncome, 0, ',', '.') : '-' }}
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        
        {{-- Footer --}}
        <div class="mt-8 pt-8 px-4 sm:px-6 flex justify-end">
            <div class="text-center">
                <p class="text-sm text-zinc-700 mb-16">TEJA, {{ $signatureDate }}</p>
                <p class="text-sm font-bold text-zinc-900 uppercase underline underline-offset-4">{{ $signatory }}</p>
                <p class="text-sm text-zinc-600 mt-1">{{ $position }}</p>
            </div>
        </div>
    </div>
</div>
