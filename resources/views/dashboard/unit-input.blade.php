<x-layouts::app :title="__('Input Transaksi Harian')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">

        <x-page-header title="Input Transaksi Harian" description="Catat pemasukan harian unit usaha Anda" />

        <livewire:unit-head.record-daily-transaction />
    </div>
</x-layouts::app>
