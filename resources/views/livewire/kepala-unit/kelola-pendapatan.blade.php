<div class="flex flex-col gap-6 max-w-4xl mx-auto w-full pb-20">

    {{-- Header --}}
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-zinc-900">Kelola Pendapatan</h1>
        <p class="text-sm text-zinc-500">
            Atur harga kategori dan tambah kategori pendapatan baru untuk unit
            <span class="font-semibold text-brand-700">{{ $unitNama }}</span>.
        </p>
    </div>

    {{-- Tombol Tambah Kategori Baru --}}
    <div class="flex justify-end">
        <button type="button" wire:click="$toggle('showTambahForm')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all active:scale-95
                {{ $showTambahForm ? 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' : 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm' }}">
            @if ($showTambahForm)
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Tutup Form
            @else
                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Kategori Baru
            @endif
        </button>
    </div>

    {{-- Form Tambah Kategori Baru --}}
    @if ($showTambahForm)
        <div class="bg-white rounded-2xl border border-brand-200 shadow-sm overflow-hidden">
            <div class="bg-brand-50 px-5 py-4 border-b border-brand-100">
                <h2 class="text-base font-bold text-brand-900 flex items-center gap-2">
                    <svg class="size-5 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kategori Pendapatan Baru
                </h2>
                <p class="text-xs text-brand-600 mt-0.5">Kategori baru akan langsung muncul di form Input Transaksi Harian.</p>
            </div>

            <form wire:submit.prevent="tambahKategori" class="p-5 flex flex-col gap-4">

                {{-- Nama Kategori --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="namaKategori" placeholder="cth: Sewa Pelampung, Tiket VIP..."
                        class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('namaKategori') border-red-400 @enderror">
                    @error('namaKategori')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tipe & Harga dalam 2 kolom --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Tipe Kategori --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-700">Tipe Kategori <span class="text-red-500">*</span></label>
                        <select wire:model.live="tipeKategori"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('tipeKategori') border-red-400 @enderror">
                            <option value="harga_x_qty">Harga × Jumlah (tiket, parkir, sewa per item)</option>
                            <option value="tahunan">Tahunan (sewa kios, kontrak per tahun)</option>
                            <option value="bebas">Bebas (nominal diisi manual saat transaksi)</option>
                        </select>
                        @error('tipeKategori')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-zinc-400">
                            @if ($tipeKategori === 'harga_x_qty') Pendapatan = harga satuan × jumlah pengunjung/item.
                            @elseif ($tipeKategori === 'tahunan') Nominal dibagi 12 per bulan untuk laporan bulanan.
                            @else Tidak ada harga tetap — nominal diisi bebas tiap kali input transaksi.
                            @endif
                        </p>
                    </div>

                    {{-- Harga (kondisional) --}}
                    @if ($tipeKategori !== 'bebas')
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-zinc-700">
                                {{ $tipeKategori === 'tahunan' ? 'Nominal per Tahun (Rp)' : 'Harga Satuan (Rp)' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm font-medium pointer-events-none">Rp</span>
                                <input type="number" wire:model="hargaKategori" min="0" step="500" placeholder="0"
                                    class="pl-10 w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('hargaKategori') border-red-400 @enderror">
                            </div>
                            @error('hargaKategori')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-zinc-400">Harga</label>
                            <div class="w-full rounded-xl border-2 border-zinc-100 bg-zinc-50 px-3.5 py-2.5 text-sm text-zinc-400 italic">
                                Tidak ada — diisi bebas saat transaksi
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Akun Pendapatan --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Masuk ke Akun <span class="text-red-500">*</span></label>
                    <select wire:model="kodeAkunKategoriId"
                        class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('kodeAkunKategoriId') border-red-400 @enderror">
                        <option value="">— Pilih Akun Pendapatan —</option>
                        @foreach ($akunPendapatan as $akun)
                            <option value="{{ $akun->id }}">{{ $akun->kode }} | {{ $akun->nama }}</option>
                        @endforeach
                    </select>
                    @error('kodeAkunKategoriId')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 text-white hover:bg-brand-700 shadow-sm active:scale-95 transition-all disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="tambahKategori">
                        <span wire:loading.remove wire:target="tambahKategori" class="flex items-center gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Simpan Kategori
                        </span>
                        <span wire:loading wire:target="tambahKategori" class="flex items-center gap-2">
                            <svg class="animate-spin size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                    <button type="button" wire:click="$toggle('showTambahForm')"
                        class="px-4 py-2.5 rounded-xl text-sm font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Daftar Kategori Existing (edit harga) --}}
    <div>
        <h2 class="text-base font-semibold text-zinc-700 mb-3 flex items-center gap-2">
            <svg class="size-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit Harga Kategori
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $editableCategories = $categories->filter(fn($c) => $c->tipe->value !== 'bebas');
                $bebasCategories = $categories->filter(fn($c) => $c->tipe->value === 'bebas');
            @endphp

            @forelse ($editableCategories as $category)
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-brand-100 flex flex-col gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900">{{ $category->nama }}</h3>
                    </div>

                    <form wire:submit.prevent="updateHarga({{ $category->id }})" class="flex flex-col gap-3 mt-auto">
                        <div>
                            <label for="price_{{ $category->id }}"
                                class="block text-sm font-medium text-zinc-700 mb-1">
                                {{ $category->tipe->value === 'tahunan' ? 'Nominal per Tahun / Baru' : 'Harga Satuan / Baru' }}
                            </label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-zinc-500 font-medium text-lg sm:text-xl">Rp</span>
                                </div>
                                <input type="number" id="price_{{ $category->id }}"
                                    wire:model="prices.{{ $category->id }}"
                                    class="pl-14 py-3 sm:py-4 block w-full rounded-2xl border-2 border-zinc-200 bg-zinc-50/50 text-zinc-900 text-lg sm:text-xl font-bold focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:bg-white transition-all shadow-sm @error('prices.' . $category->id) border-red-400 text-red-900 @enderror [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                    placeholder="0" required min="1">
                            </div>
                            @error('prices.' . $category->id)
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @if (session()->has('success_' . $category->id))
                                <p class="mt-1 text-xs text-brand-600 flex items-center gap-1">
                                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    {{ session('success_' . $category->id) }}
                                </p>
                            @endif
                        </div>

                        <button type="submit"
                            class="w-full py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-zinc-900 bg-brand-300 hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 transition-all active:scale-[0.98] flex justify-center items-center gap-2"
                            wire:loading.attr="disabled" wire:target="updateHarga({{ $category->id }})">
                            <span wire:loading.remove wire:target="updateHarga({{ $category->id }})">Update Harga</span>
                            <span wire:loading wire:target="updateHarga({{ $category->id }})" class="flex items-center gap-2">
                                <svg class="animate-spin size-4 text-zinc-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </form>
                </div>
            @empty
                <div class="md:col-span-2 bg-zinc-50 rounded-2xl border border-zinc-200 p-8 text-center">
                    <p class="text-zinc-500 text-sm">Belum ada kategori dengan harga tetap. Tambahkan kategori baru di atas.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Kategori Tipe Bebas (read-only, info saja) --}}
    @if ($bebasCategories->count() > 0)
        <div>
            <h2 class="text-base font-semibold text-zinc-500 mb-3 flex items-center gap-2">
                <svg class="size-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                Kategori Nominal Bebas
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ($bebasCategories as $category)
                    <div class="bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 flex flex-col gap-1">
                        <span class="text-sm font-semibold text-zinc-700">{{ $category->nama }}</span>
                        <span class="text-xs text-zinc-400">Bebas — nominal diisi tiap transaksi</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
