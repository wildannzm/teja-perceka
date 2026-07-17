<x-layouts::app :title="__('Dashboard Unit Wisata')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

        {{-- Header kartu --}}
        <div class="rounded-xl border border-brand-100 bg-white dark:bg-zinc-900 dark:border-zinc-700 p-5 sm:p-8">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-block h-10 w-1.5 rounded-full bg-brand-700 shrink-0"></span>
                <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 dark:text-brand-300 leading-tight">
                    Dashboard Kepala Unit Wisata
                </h1>
            </div>
            <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400 leading-relaxed">
                Selamat datang di sistem pembukuan digital BUMDes Teja Perceka. Halaman ini akan menampilkan rekap pemasukan harian unit wisata Anda.
            </p>
        </div>

        <livewire:kepala-unit.input-transaksi-harian />

    </div>
</x-layouts::app>
