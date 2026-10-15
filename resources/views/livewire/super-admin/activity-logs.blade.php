<div class="space-y-6">
    <x-page-header title="Audit Log Aktivitas Sistem" description="Catatan lengkap mengenai aksi, login, dan penyamaran (impersonation) oleh pengguna.">
        <x-slot:actions>
            <button type="button" wire:click="confirmClear('filtered')" @disabled($logs->total() === 0)
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-700 bg-white border border-red-200 rounded-xl hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <flux:icon icon="trash" class="size-4" />
                <span>Hapus</span>
            </button>
            <button type="button" wire:click="confirmClear('all')" @disabled($logs->total() === 0)
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 border border-transparent rounded-xl hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <flux:icon icon="arrow-path" class="size-4" />
                <span>Reset Semua</span>
            </button>
        </x-slot:actions>
    </x-page-header>


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
                wire:model.live.debounce.250ms="activityType" 
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
        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Clear confirmation modal --}}
    @if ($showClearModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showClearModal', false)" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="size-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900">
                                {{ $clearMode === 'all' ? 'Reset Semua Log?' : 'Hapus Log?' }}
                            </h2>
                            <p class="text-sm text-zinc-500 mt-0.5">
                                {{ $clearMode === 'all'
                                    ? 'Seluruh data log aktivitas akan dihapus permanen dan tidak dapat dikembalikan.'
                                    : 'Log yang tampil sesuai pencarian & filter saat ini akan dihapus permanen.' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="$set('showClearModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeClear" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors disabled:opacity-60">
                            Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
