<x-layouts::app :title="__('Dashboard BUMDes')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

        <div class="rounded-xl border border-brand-100 bg-white dark:bg-zinc-900 dark:border-zinc-700 p-5 sm:p-8">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-block h-10 w-1.5 rounded-full bg-brand-700 shrink-0"></span>
                <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 dark:text-brand-300 leading-tight">
                    Dashboard Direktur BUMDes
                </h1>
            </div>
            <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400 leading-relaxed">
                Selamat datang di sistem pembukuan digital BUMDes Teja Perceka. Halaman ini akan menampilkan rekap performa dan laporan manajerial seluruh unit wisata untuk Direktur BUMDes.
            </p>
        </div>

        <div class="flex-1 rounded-xl border border-dashed border-brand-200 dark:border-zinc-700 bg-brand-50/50 dark:bg-zinc-900 flex flex-col items-center justify-center gap-3 py-16 px-6 text-center">
            <svg class="size-10 text-brand-300 dark:text-brand-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
            </svg>
            <p class="text-sm text-brand-400 dark:text-brand-600 font-medium">
                Dashboard manajerial & rekap seluruh unit akan ditampilkan di sini (Modul 4)
            </p>
        </div>

    </div>
</x-layouts::app>
