<x-layouts::app :title="__('Edit Transaksi')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">

                <x-page-header title="Edit Transaksi Harian" description="Ubah data pemasukan yang sudah diinput sebelumnya" accent="bg-amber-400">
            <x-slot:actions>
                <flux:button variant="ghost" :href="route('riwayat-rekap')" wire:navigate
                        class="w-full sm:w-auto shrink-0" icon="arrow-left">
                        Kembali ke Riwayat
                    </flux:button>
            </x-slot:actions>
        </x-page-header>

        <livewire:kepala-unit.input-transaksi-harian :editId="$editId" />

    </div>
</x-layouts::app>
