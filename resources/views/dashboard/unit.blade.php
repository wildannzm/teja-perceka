<x-layouts::app :title="__('Dashboard Unit Wisata')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

        {{-- Header kartu --}}
        <div class="rounded-xl border border-brand-100 bg-white p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="inline-block h-10 w-1.5 rounded-full bg-brand-700 shrink-0"></span>
                    <div class="min-w-0">
                        <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 leading-tight">
                            Dashboard Kepala Unit
                        </h1>
                        <p class="text-sm text-zinc-500 mt-0.5 leading-relaxed">
                            Sistem pembukuan digital BUMDes Teja Perceka
                        </p>
                    </div>
                </div>
                <flux:button variant="primary" :href="route('unit.riwayat-transaksi')" wire:navigate
                    class="w-full sm:w-auto shrink-0" icon="document-chart-bar">
                    Riwayat &amp; Rekap
                </flux:button>
            </div>
        </div>

        <livewire:kepala-unit.input-transaksi-harian />

    </div>
</x-layouts::app>
