<x-layouts::app :title="__('Edit Transaksi')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">

        <x-page-header title="Edit Transaksi Harian" description="Ubah data pemasukan yang sudah diinput sebelumnya" accent="bg-amber-400" />

        <livewire:unit-head.record-daily-transaction :editId="$editId" />

    </div>
</x-layouts::app>
