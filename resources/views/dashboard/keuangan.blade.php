<x-layouts::app :title="__('Dashboard Keuangan')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

        <div class="rounded-xl border border-brand-100 bg-white p-5 sm:p-8">
            <div class="flex items-center gap-3 mb-3">
                <span class="inline-block h-10 w-1.5 rounded-full bg-brand-700 shrink-0"></span>
                <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 leading-tight">
                    Dashboard Keuangan
                </h1>
            </div>
            <p class="text-sm sm:text-base text-zinc-500 leading-relaxed">
                Selamat datang di sistem pembukuan digital BUMDes Teja Perceka. Halaman ini akan menampilkan rekap
                keuangan seluruh unit wisata untuk sekretaris dan bendahara.
            </p>
        </div>

        <div
            class="flex-1 rounded-xl border border-dashed border-brand-200 bg-brand-50/50 flex flex-col items-center justify-center gap-3 py-16 px-6 text-center">
            <svg class="size-10 text-brand-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" />
            </svg>
            <p class="text-sm text-brand-400 font-medium">
                Rekap keuangan & laporan akan ditampilkan di sini (Modul 4)
            </p>
        </div>

    </div>
</x-layouts::app>
