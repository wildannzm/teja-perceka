<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-zinc-900">Dashboard Super Admin</h1>
            <p class="text-sm text-zinc-500">Kelola seluruh akses pengguna, penyamaran (*impersonation*), dan log aktivitas sistem secara realtime.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.users') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors shadow-sm">
                <flux:icon icon="users" class="size-4" />
                <span>Manajemen User</span>
            </a>
            <a href="{{ route('super-admin.activity-logs') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 font-semibold text-sm rounded-xl transition-colors shadow-sm">
                <flux:icon icon="clock" class="size-4 text-zinc-500" />
                <span>Log Aktivitas</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total Pengguna</p>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ number_format($totalUsers) }}</h3>
                </div>
                <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <flux:icon icon="users" class="size-6" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Unit Wisata</p>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ number_format($totalUnits) }}</h3>
                </div>
                <div class="p-3 bg-cyan-100 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 rounded-xl">
                    <flux:icon icon="building-office-2" class="size-6" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Jumlah Role</p>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ number_format($totalRoles) }}</h3>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl">
                    <flux:icon icon="key" class="size-6" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Aktivitas Hari Ini</p>
                    <h3 class="text-2xl font-bold text-zinc-900 dark:text-white mt-1">{{ number_format($todayActivities) }}</h3>
                </div>
                <div class="p-3 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl">
                    <flux:icon icon="bolt" class="size-6" />
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Impersonate -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Log Activity -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Log Aktivitas Terbaru</h2>
                        <p class="text-xs text-zinc-500">Jejak audit aksi pengguna sistem secara realtime</p>
                    </div>
                    <a href="{{ route('super-admin.activity-logs') }}" wire:navigate class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua →</a>
                </div>

                <div class="space-y-3">
                    @forelse($recentActivities as $log)
                        <div class="flex items-start gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-800">
                            <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 mt-0.5">
                                @if(str_contains($log->activity_type, 'impersonate'))
                                    <flux:icon icon="user-plus" class="size-4" />
                                @else
                                    <flux:icon icon="document-text" class="size-4" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-zinc-900 dark:text-white truncate">
                                        {{ $log->user ? $log->user->name : 'Sistem/Tamu' }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400">
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-xs text-zinc-600 dark:text-zinc-300 mt-0.5">{{ $log->description }}</p>
                                @if($log->ip_address)
                                    <span class="text-[10px] text-zinc-400 font-mono mt-1 inline-block">IP: {{ $log->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-zinc-400 text-sm">Belum ada log aktivitas tercatat.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Users & Impersonation Shortcut -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Pengguna Terbaru</h2>
                    <p class="text-xs text-zinc-500">Pintas impersonasi pengguna</p>
                </div>
                <a href="{{ route('super-admin.users') }}" wire:navigate class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Kelola →</a>
            </div>

            <div class="space-y-3">
                @foreach($recentUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-800">
                        <div class="min-w-0 pr-2">
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-white truncate">{{ $user->name }}</h4>
                            <p class="text-[11px] text-zinc-500 truncate">{{ $user->email }}</p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="inline-block px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 rounded">
                                    {{ $user->getRoleNames()->first() ?? 'User' }}
                                </span>
                            </div>
                        </div>

                        @if(Auth::id() !== $user->id)
                            <form method="POST" action="{{ route('impersonate.start', $user) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-lg shadow-sm transition-all flex items-center gap-1 shrink-0">
                                    <flux:icon icon="user-plus" class="size-3" />
                                    <span>Penyamaran</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
