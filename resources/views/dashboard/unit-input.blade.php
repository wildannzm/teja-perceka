<x-layouts::app :title="__('Input Transaksi Harian')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">

        <x-page-header title="Input Transaksi Harian" description="Catat pemasukan harian unit usaha Anda">
            <x-slot:actions>
                <flux:button variant="ghost" :href="route('dashboard.unit')" wire:navigate class="w-full sm:w-auto shrink-0"
                    icon="arrow-left">
                    Kembali ke Dashboard
                </flux:button>
            </x-slot:actions>
        </x-page-header>

        <livewire:kepala-unit.input-transaksi-harian />
    </div>
</x-layouts::app>
