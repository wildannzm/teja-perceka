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

        {{-- Placeholder area konten --}}
        <div class="flex-1 rounded-xl border border-dashed border-brand-200 dark:border-zinc-700 bg-brand-50/50 dark:bg-zinc-900 flex flex-col items-center justify-center gap-3 py-16 px-6 text-center">
            <svg class="size-10 text-brand-300 dark:text-brand-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
            </svg>
            <p class="text-sm text-brand-400 dark:text-brand-600 font-medium">
                Form input transaksi harian akan ditampilkan di sini (Modul 3)
            </p>
        </div>

    </div>
</x-layouts::app>
