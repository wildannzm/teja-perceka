<div>
    <div class="flex h-full w-full flex-col gap-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-neutral-900">Rekap Semua Unit Usaha</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl border border-neutral-200">
                <h3 class="text-sm font-medium text-neutral-500">Total Pemasukan (Bulan Ini)</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900">Rp {{ number_format($pemasukan, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-neutral-200">
                <h3 class="text-sm font-medium text-neutral-500">Total Pengeluaran (Bulan Ini)</h3>
                <p class="mt-2 text-3xl font-bold text-neutral-900">Rp {{ number_format($pengeluaran, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-neutral-200">
                <h3 class="text-sm font-medium text-neutral-500">Saldo Akhir</h3>
                <p class="mt-2 text-3xl font-bold text-brand-600">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-neutral-200 flex-1">
            <h2 class="text-lg font-semibold mb-4 text-neutral-900">Grafik Rekapitulasi (Segera Hadir)</h2>
            <div class="h-64 w-full rounded-lg bg-neutral-100 flex items-center justify-center">
                <p class="text-neutral-500">Data belum tersedia.</p>
            </div>
        </div>
    </div>
</div>
