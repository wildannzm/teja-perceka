<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-10">

    <x-page-header title="Daftar Aset BUMDes" description="Inventaris aset milik BUMDes Teja Perceka.">
        <x-slot:actions>
            @if($this->canManage)
            <button
                wire:click="openCreate"
                id="btn-tambah-aset"
                class="flex items-center gap-1.5 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2.5 rounded-xl transition-all active:scale-95 shadow-sm"
            >
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Aset
            </button>
        @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Search --}}
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-4 sm:p-5">
        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-zinc-400 pointer-events-none"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama aset atau keterangan..."
                id="input-cari-aset"
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border-2 border-zinc-200 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors"
            >
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
        @if($this->assets->isEmpty())
            <div class="py-16 px-6 text-center flex flex-col items-center justify-center">
                <div class="size-14 rounded-full bg-zinc-100 flex items-center justify-center mb-4">
                    <svg class="size-7 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-zinc-900 mb-1">
                    {{ $search ? 'Aset Tidak Ditemukan' : 'Belum Ada Aset' }}
                </h3>
                <p class="text-sm text-zinc-500 max-w-xs mx-auto">
                    {{ $search ? 'Coba kata kunci yang berbeda.' : 'Klik tombol "Tambah Aset" untuk menambahkan inventaris aset BUMDes.' }}
                </p>
            </div>
        @else
            {{-- Desktop table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-zinc-50 border-b border-zinc-200">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs">No</th>
                            <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs">Nama Aset</th>
                            <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs text-center">Jumlah</th>
                            <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs">Harga Perolehan</th>
                            <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs">Keterangan</th>
                            @if($this->canManage)
                                <th class="px-5 py-3.5 font-semibold text-zinc-500 uppercase tracking-wider text-xs text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($this->assets as $index => $asset)
                            <tr class="hover:bg-zinc-50 transition-colors">
                                <td class="px-5 py-4 text-zinc-400 text-xs">
                                    {{ ($this->assets->currentPage() - 1) * $this->assets->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-zinc-900">{{ $asset->nama_aset }}</p>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-brand-50 text-brand-700 font-bold text-sm border border-brand-200">
                                        {{ number_format($asset->jumlah, 0, ',', '.') }}
                                        <span class="font-normal text-xs text-brand-500">{{ $asset->satuan }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($asset->harga > 0)
                                        <span class="font-semibold text-zinc-800">Rp {{ number_format($asset->harga, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-zinc-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-zinc-500 max-w-xs">
                                    {{ $asset->keterangan ?? '-' }}
                                </td>
                                @if($this->canManage)
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                wire:click="openEdit({{ $asset->id }})"
                                                id="btn-edit-aset-{{ $asset->id }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 border border-zinc-300 px-3 py-1.5 rounded-lg transition-all active:scale-95"
                                            >
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $asset->id }})"
                                                id="btn-hapus-aset-{{ $asset->id }}"
                                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 p-1.5 rounded-lg transition-all active:scale-95"
                                            >
                                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.72 0L9 9m1.74-2.82h6.52M10 11v6M14 11v6m4.33-9.52-.77 10.8c-.08 1.1-1 1.95-2.1 1.95H8.54c-1.1 0-2.02-.85-2.1-1.95L5.67 8.28m11.23-.78V5.3c0-1.11-.9-2-2-2h-3.8c-1.1 0-2 .89-2 2v2.2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile card --}}
            <div class="sm:hidden flex flex-col divide-y divide-zinc-100">
                @foreach($this->assets as $asset)
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-zinc-900 text-sm">{{ $asset->nama_aset }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">{{ $asset->keterangan ?? 'Tidak ada keterangan' }}</p>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 font-bold text-xs border border-brand-200">
                                    {{ number_format($asset->jumlah, 0, ',', '.') }} {{ $asset->satuan }}
                                </span>
                                @if($asset->harga > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-zinc-100 text-zinc-700 text-xs border border-zinc-200">
                                        Rp {{ number_format($asset->harga, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($this->canManage)
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    wire:click="openEdit({{ $asset->id }})"
                                    class="inline-flex items-center justify-center size-8 rounded-lg border border-zinc-200 bg-white text-zinc-700 hover:bg-zinc-50 transition-all active:scale-95"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                    </svg>
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $asset->id }})"
                                    class="inline-flex items-center justify-center size-8 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition-all active:scale-95"
                                >
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.34 9m-4.72 0L9 9m1.74-2.82h6.52M10 11v6M14 11v6m4.33-9.52-.77 10.8c-.08 1.1-1 1.95-2.1 1.95H8.54c-1.1 0-2.02-.85-2.1-1.95L5.67 8.28m11.23-.78V5.3c0-1.11-.9-2-2-2h-3.8c-1.1 0-2 .89-2 2v2.2" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($this->assets->hasPages())
                <div class="px-5 py-4 border-t border-zinc-100">
                    {{ $this->assets->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Total summary --}}
    @if(!$this->assets->isEmpty())
        <div class="bg-brand-50 border border-brand-200 rounded-2xl px-5 py-4 flex items-center justify-between">
            <span class="text-sm font-medium text-brand-800">Total Jenis Aset</span>
            <span class="text-lg font-bold text-brand-900">{{ $this->assets->total() }} jenis</span>
        </div>
    @endif

    {{-- Add/edit asset modal --}}
    @if($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data
            x-init="$el.focus()"
            @keydown.escape.window="$wire.closeModal()"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                wire:click="closeModal"
            ></div>

            {{-- Modal panel --}}
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                {{-- Modal header --}}
                <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between bg-zinc-50">
                    <h2 class="text-base font-semibold text-zinc-900">
                        {{ $editingId ? 'Edit Aset' : 'Tambah Aset Baru' }}
                    </h2>
                    <button
                        wire:click="closeModal"
                        class="size-8 flex items-center justify-center rounded-lg text-zinc-400 hover:text-zinc-700 hover:bg-zinc-200 transition-colors"
                    >
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal body --}}
                <form wire:submit="save" class="px-6 py-5 flex flex-col gap-4">
                    {{-- Asset name --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="modal-nama-aset" class="text-sm font-medium text-zinc-700">
                            Nama Aset <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="modal-nama-aset"
                            wire:model="nama_aset"
                            placeholder="Contoh: Mesin Pompa Air, Pelampung Situ..."
                            autofocus
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('nama_aset') border-red-400 @enderror"
                        >
                        @error('nama_aset')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Quantity & unit --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="modal-jumlah" class="text-sm font-medium text-zinc-700">
                                Jumlah <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                id="modal-jumlah"
                                wire:model="jumlah"
                                min="1"
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('jumlah') border-red-400 @enderror"
                            >
                            @error('jumlah')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label for="modal-satuan" class="text-sm font-medium text-zinc-700">
                                Satuan <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="modal-satuan"
                                wire:model="satuan"
                                placeholder="unit, buah, set..."
                                class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('satuan') border-red-400 @enderror"
                            >
                            @error('satuan')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Acquisition cost --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="modal-harga" class="text-sm font-medium text-zinc-700">
                            Harga Perolehan <span class="text-zinc-400 font-normal">(opsional)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm font-medium pointer-events-none">Rp</span>
                            <input
                                type="number"
                                id="modal-harga"
                                wire:model="harga"
                                min="0"
                                step="1000"
                                placeholder="0"
                                class="pl-10 w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('harga') border-red-400 @enderror"
                            >
                        </div>
                        @error('harga')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="flex flex-col gap-1.5">
                        <label for="modal-keterangan" class="text-sm font-medium text-zinc-700">
                            Keterangan <span class="text-zinc-400 font-normal">(opsional)</span>
                        </label>
                        <textarea
                            id="modal-keterangan"
                            wire:model="keterangan"
                            rows="3"
                            placeholder="Kondisi, lokasi, atau catatan tambahan..."
                            class="w-full rounded-xl border-2 border-zinc-200 text-zinc-900 px-3.5 py-2.5 text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors resize-none @error('keterangan') border-red-400 @enderror"
                        ></textarea>
                        @error('keterangan')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="flex-1 py-2.5 rounded-xl border-2 border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            id="btn-simpan-aset"
                            class="flex-1 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 active:scale-[0.98] transition-all disabled:opacity-70 disabled:cursor-wait"
                        >
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Aset' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div wire:click="$set('showDeleteModal', false)" class="absolute inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col gap-0 overflow-hidden z-10">
                <div class="px-6 py-5 flex flex-col gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">Konfirmasi Hapus</h2>
                        <div class="mt-2 text-sm text-zinc-600">
                            <p>Yakin ingin menghapus data ini?</p>
                            <p>Data yang dihapus tidak dapat dikembalikan dan mungkin mempengaruhi kalkulasi laporan.</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl border border-zinc-200 text-sm font-semibold text-zinc-700 hover:bg-zinc-50 transition-colors">
                            Batal
                        </button>
                        <button type="button" wire:click="executeDelete" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
