<x-layouts::app :title="__('Dashboard Unit Usaha')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

                <x-page-header title="Dashboard Kepala Unit" description="Sistem pembukuan digital BUMDes Teja Perceka">
            <x-slot:actions>
                <flux:button variant="primary" :href="route('unit.riwayat-transaksi')" wire:navigate
                    class="w-full sm:w-auto shrink-0" icon="document-chart-bar">
                    Riwayat &amp; Rekap
                </flux:button>
            </x-slot:actions>
        </x-page-header>

        <livewire:kepala-unit.input-transaksi-harian />

    </div>
</x-layouts::app>
