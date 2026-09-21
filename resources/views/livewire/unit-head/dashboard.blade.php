<div class="flex flex-col gap-5 max-w-full mx-auto w-full">

    <x-page-header :title="'Dashboard - ' . ($unit->name ?? 'Unit Usaha')" />

    {{-- Unsubmitted transaction alert --}}
    @if (!$isCurrentPeriodSubmitted)
        <div
            class="rounded-xl border border-red-200 bg-red-50 p-4 sm:p-5 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3 min-w-0">
                <div class="size-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="size-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-red-900 leading-tight">
                        Peringatan: Belum Input Transaksi!
                    </h2>
                    <p class="text-sm text-red-700 mt-0.5 leading-relaxed">
                        Anda belum mengisi transaksi untuk
                        {{ $unit->input_frequency === 'weekly' ? 'week' : 'hari' }} ini. Silakan segera isi data
                        transaksi.
                    </p>
                </div>
            </div>
            <flux:button variant="danger" :href="route('unit.record-transaction')" wire:navigate
                class="w-full sm:w-auto shrink-0" icon="pencil-square">
                Input Sekarang
            </flux:button>
        </div>
    @endif

    {{-- Today date --}}

    <p class="text-xs text-zinc-400 px-1">
        {{ \Illuminate\Support\Carbon::now()->translatedFormat('l, d F Y') }}
    </p>

    {{-- 4 Stat cards --}}
    {{-- 4 Stat cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Today --}}
        <div class="bg-white rounded-2xl border border-brand-100 p-5 shadow-sm flex flex-col gap-2 transition-shadow hover:shadow-md">
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-lg bg-brand-100 flex items-center justify-center shrink-0">
                    <svg class="size-4 text-brand-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Hari Ini</span>
            </div>
            <div class="text-xl font-bold text-brand-900 leading-tight">
                Rp {{ number_format($this->todayIncome, 0, ',', '.') }}
            </div>
            <div class="text-xs text-zinc-400">{{ \Illuminate\Support\Carbon::today()->translatedFormat('d M Y') }}
            </div>
        </div>

        {{-- This week --}}
        <div class="bg-white rounded-2xl border border-brand-100 p-5 shadow-sm flex flex-col gap-2 transition-shadow hover:shadow-md">
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-lg bg-brand-100 flex items-center justify-center shrink-0">
                    <svg class="size-4 text-brand-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Minggu Ini</span>
            </div>
            <div class="text-xl font-bold text-brand-900 leading-tight">
                Rp {{ number_format($this->thisWeekIncome, 0, ',', '.') }}
            </div>
            <div class="text-xs text-zinc-400">
                {{ \Illuminate\Support\Carbon::now()->startOfWeek()->format('d') }} -
                {{ \Illuminate\Support\Carbon::now()->endOfWeek()->translatedFormat('d M Y') }}
            </div>
        </div>

        {{-- This month --}}
        <div class="bg-brand-300 rounded-2xl border border-brand-400 p-5 shadow-sm flex flex-col gap-2 transition-shadow hover:shadow-md">
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-lg bg-white/40 flex items-center justify-center shrink-0">
                    <svg class="size-4 text-brand-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-brand-800 uppercase tracking-wider">Bulan Ini</span>
            </div>
            <div class="text-xl font-bold text-brand-950 leading-tight">
                Rp {{ number_format($this->thisMonthIncome, 0, ',', '.') }}
            </div>
            <div class="text-xs text-brand-700">{{ \Illuminate\Support\Carbon::now()->translatedFormat('F Y') }}</div>
        </div>

        {{-- This year --}}
        <div class="bg-white rounded-2xl border border-brand-100 p-5 shadow-sm flex flex-col gap-2 transition-shadow hover:shadow-md">
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-lg bg-brand-100 flex items-center justify-center shrink-0">
                    <svg class="size-4 text-brand-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                </div>
                <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Tahun Ini</span>
            </div>
            <div class="text-xl font-bold text-brand-900 leading-tight">
                Rp {{ number_format($this->thisYearIncome, 0, ',', '.') }}
            </div>
            <div class="text-xs text-zinc-400">{{ \Illuminate\Support\Carbon::now()->format('Y') }}</div>
        </div>
    </div>

    {{-- Recap chart --}}
    <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex flex-col w-full">
        <h2 class="text-lg font-bold text-zinc-900 mb-4">Grafik Rekapitulasi</h2>
        <div class="w-full relative min-h-[350px]">
            <canvas id="recapChart"></canvas>
        </div>
    </div>

    {{-- Latest transactions --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100">
            <div>
                <h2 class="text-sm font-semibold text-zinc-800">7 Transaksi Terakhir</h2>
                <p class="text-xs text-zinc-400 mt-0.5">{{ $this->thisMonthTransactionCount }} transaksi bulan ini</p>
            </div>
            <flux:button size="sm" variant="ghost" :href="route('unit.transaction-history')" wire:navigate
                icon="arrow-right">
                Lihat semua
            </flux:button>
        </div>

        @forelse ($this->latestTransactions as $trx)
            <div
                class="flex items-center justify-between px-5 py-3.5 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-brand-50 flex items-center justify-center shrink-0">
                        <svg class="size-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-zinc-800">
                            {{ $trx->transaction_date instanceof \Illuminate\Support\Carbon ? $trx->transaction_date->translatedFormat('d M Y') : \Illuminate\Support\Carbon::parse($trx->transaction_date)->translatedFormat('d M Y') }}
                        </div>
                        <div class="text-xs text-zinc-400">
                            {{ $trx->endDate ? 'Mingguan' : 'Harian' }}
                        </div>
                    </div>
                </div>
                <div class="text-sm font-semibold text-brand-800">
                    Rp {{ number_format($trx->total_income, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center text-zinc-400 text-sm">
                <svg class="size-10 mx-auto mb-3 text-zinc-300" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                Belum ada transaksi yang tercatat
            </div>
        @endforelse
    </div>

</div>

@script
<script>
    const ctx = document.getElementById('recapChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Pemasukan',
                    data: @json($incomeChart),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Pengeluaran',
                    data: @json($expenseChart),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 1000000,
                    ticks: {
                        callback: function(value) {
                            if (value === 0) return '0';
                            let valInJuta = value / 1000000;
                            return 'Rp ' + valInJuta.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Jt';
                        }
                    }
                }
            }
        }
    });
</script>
@endscript

