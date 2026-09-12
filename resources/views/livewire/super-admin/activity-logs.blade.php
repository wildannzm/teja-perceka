<div class="space-y-6">
    <x-page-header title="Audit Log Aktivitas Sistem" description="Catatan lengkap mengenai aksi, login, dan penyamaran (impersonation) oleh pengguna." />


    <!-- Filters & Search -->
    <div class="bg-white p-4 border border-zinc-200 rounded-2xl flex flex-col md:flex-row gap-4 items-center justify-between shadow-sm">
        <div class="w-full md:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari deskripsi, user, atau IP..." 
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl px-3.5 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
        </div>

        <div class="w-full md:w-56">
            <select 
                wire:model.live="activityType" 
                class="w-full bg-zinc-50 border border-zinc-300 rounded-xl px-3.5 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                <option value="">Semua Tipe Aktivitas</option>
                @foreach($activityTypes as $type)
                    <option value="{{ $type }}">{{ Str::headline($type) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Log table -->
    <div class="bg-white border border-zinc-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-500 uppercase text-[11px] font-semibold tracking-wider border-b border-zinc-200">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-5 py-3.5">Tipe Aktivitas</th>
                        <th class="px-5 py-3.5">Deskripsi</th>
                        <th class="px-5 py-3.5 text-right">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 text-zinc-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-zinc-50/80:bg-zinc-800/40 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-xs text-zinc-500">
                                <div>{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</div>
                                <div class="text-[11px] text-zinc-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($log->user)
                                    <div class="font-bold text-zinc-900 text-xs">{{ $log->user->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $log->user->email }}</div>
                                @else
                                    <span class="text-xs text-zinc-400 italic">Sistem/Guest</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ str_contains($log->activity_type, 'impersonate') ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ Str::headline($log->activity_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs text-zinc-800">{{ $log->description }}</div>
                                @if($log->properties && count($log->properties) > 0)
                                    <details class="mt-1">
                                        <summary class="text-[11px] text-emerald-600 cursor-pointer hover:underline">Lihat Detail Json</summary>
                                        <pre class="text-[10px] bg-zinc-100 p-2 rounded mt-1 overflow-x-auto font-mono text-zinc-700">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                                    </details>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="font-mono text-xs text-zinc-500">{{ $log->ip_address ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-zinc-400 text-sm">
                                Belum ada log aktivitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-5 py-4 border-t border-zinc-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
