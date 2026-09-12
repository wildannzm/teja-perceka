<div class="space-y-6">
    <x-page-header title="Manajemen Pengguna & Impersonasi" description="Daftar seluruh akun pengguna sistem BUMDes. Gunakan opsi penyamaran (impersonate) untuk masuk ke akun terkait." />


    <!-- Filters & Search -->
    <div class="bg-white p-4 border border-zinc-200 rounded-2xl flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="w-full md:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari nama atau email pengguna..." 
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl px-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
        </div>

        <div class="w-full md:w-56">
            <select 
                wire:model.live="selectedRole" 
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl px-3.5 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role }}">{{ str_replace('_', ' ', Str::title($role)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- User Table -->
    <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-500 uppercase text-[11px] font-semibold tracking-wider border-b border-zinc-200">
                    <tr>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Role</th>
                        <th class="px-5 py-3.5">Unit Wisata</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi Impersonasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-zinc-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50/80:bg-zinc-800/40 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ $user->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-zinc-900 truncate">{{ $user->name }}</div>
                                        <div class="text-xs text-zinc-500 truncate">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $user->getRoleNames()->first() ?? 'Tidak ada Role' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-zinc-600">
                                    {{ $user->unitWisata ? $user->unitWisata->nama : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($user->is_active ?? true)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600">
                                        <span class="size-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-zinc-400">
                                        <span class="size-1.5 rounded-full bg-zinc-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if(Auth::id() === $user->id)
                                    <span class="text-xs text-zinc-400 italic">Akun Anda Sendiri</span>
                                @else
                                    <form method="POST" action="{{ route('impersonate.start', $user) }}" class="inline-block">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs rounded-xl shadow-sm transition-all active:scale-95"
                                        >
                                            <flux:icon icon="user-plus" class="size-3.5" />
                                            <span>Penyamaran (*Impersonate*)</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-zinc-400 text-sm">
                                Tidak ada pengguna yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-zinc-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
