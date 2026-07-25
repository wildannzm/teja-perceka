<div>
    <div class="flex h-full w-full flex-col gap-6 max-w-7xl mx-auto pb-10">
        
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-zinc-900">Dashboard</h1>
            <p class="text-sm text-zinc-500">Tinjauan ringkas performa keuangan seluruh unit BUMDes pada bulan ini.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Pemasukan --}}
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex items-start gap-4 transition-shadow hover:shadow-md">
                <div class="size-12 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold text-zinc-500">Total Pemasukan (Bulan Ini)</h3>
                    <p class="mt-1 text-2xl font-bold text-zinc-900">Rp {{ number_format($pemasukan, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Pengeluaran --}}
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex items-start gap-4 transition-shadow hover:shadow-md">
                <div class="size-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold text-zinc-500">Total Pengeluaran (Bulan Ini)</h3>
                    <p class="mt-1 text-2xl font-bold text-zinc-900">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Saldo --}}
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex items-start gap-4 transition-shadow hover:shadow-md">
                <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <h3 class="text-sm font-semibold text-zinc-500">Saldo Akhir</h3>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex-1 flex flex-col">
            <h2 class="text-lg font-bold text-zinc-900 mb-4">Grafik Rekapitulasi</h2>
            <div class="flex-1 min-h-[350px] w-full rounded-xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 flex flex-col items-center justify-center text-center p-6">
                <div class="size-16 rounded-full bg-white border border-zinc-100 flex items-center justify-center text-zinc-300 mb-4 shadow-sm">
                    <svg class="size-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-zinc-900 mb-1">Visualisasi Data Belum Tersedia</h3>
                <p class="text-sm text-zinc-500 max-w-sm">Grafik rekapitulasi interaktif akan segera hadir untuk memantau performa keuangan.</p>
            </div>
        </div>
    </div>
</div>
