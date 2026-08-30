<x-layouts::app :title="__('Edit Transaksi')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

        {{-- Header --}}
        <div class="mx-auto w-full">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="inline-block h-10 w-1.5 rounded-full bg-amber-400 shrink-0"></span>
                        <div class="min-w-0">
                            <h1 class="text-xl sm:text-2xl font-semibold text-amber-900 leading-tight">
                                Edit Transaksi Harian
                            </h1>
                            <p class="text-sm text-amber-700 mt-0.5 leading-relaxed">
                                Ubah data pemasukan yang sudah diinput sebelumnya
                            </p>
                        </div>
                    </div>
                    <flux:button variant="ghost" :href="route('riwayat-rekap')" wire:navigate
                        class="w-full sm:w-auto shrink-0" icon="arrow-left">
                        Kembali ke Riwayat
                    </flux:button>
                </div>
            </div>
        </div>

        <livewire:kepala-unit.input-transaksi-harian :editId="$editId" />

    </div>
</x-layouts::app>
