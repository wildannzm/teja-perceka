<div class="flex flex-col gap-6 max-w-full mx-auto w-full pb-20">

    <x-page-header title="Kelola Pendapatan" />

    {{-- Add new category button --}}
    <div class="flex justify-end">
        <button type="button" wire:click="$toggle('showCreateForm')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all active:scale-95
                {{ $showCreateForm ? 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' : 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm' }}">
            @if ($showCreateForm)
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

    {{-- New category form --}}
    @if ($showCreateForm)
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

            <form wire:submit.prevent="createCategory" class="p-5 flex flex-col gap-4">

                {{-- Category name --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="categoryName" placeholder="cth: Sewa Pelampung, Tiket VIP..."
                        class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('categoryName') border-red-400 @enderror">
                    @error('categoryName')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type & price in 2 columns --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Category type --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-700">Tipe Kategori <span class="text-red-500">*</span></label>
                        <select wire:model.live.debounce.250ms="categoryType"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('categoryType') border-red-400 @enderror">
                            <option value="harga_x_qty">Harga × Jumlah (tiket, parkir, sewa per item)</option>
                            <option value="tahunan">Tahunan (sewa kios, kontrak per tahun)</option>
                            <option value="bebas">Bebas (nominal diisi manual saat transaksi)</option>
                        </select>
                        @error('categoryType')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-zinc-400">
                            @if ($categoryType === 'harga_x_qty') Pendapatan = harga satuan × jumlah pengunjung/item.
                            @elseif ($categoryType === 'tahunan') Nominal dibagi 12 per bulan untuk laporan bulanan.
                            @else Tidak ada harga tetap — nominal diisi bebas tiap kali input transaksi.
                            @endif
                        </p>
                    </div>

                    {{-- Price (conditional) --}}
                    @if ($categoryType !== 'bebas')
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-semibold text-zinc-700">
                                {{ $categoryType === 'tahunan' ? 'Harga per Tahun (Rp)' : 'Harga Satuan (Rp)' }}
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-zinc-500 text-sm font-medium pointer-events-none">Rp</span>
                                <input type="text" inputmode="numeric" wire:model="categoryPrice" placeholder="0"
                                    x-data
                                    x-init="$el.value = $el.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')"
                                    x-on:input="$el.value = $el.value.replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')"
                                    class="pl-10 w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('categoryPrice') border-red-400 @enderror">
                            </div>
                            @error('categoryPrice')
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

                {{-- Income account --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold text-zinc-700">Masuk ke Akun <span class="text-red-500">*</span></label>
                    <select wire:model="categoryAccountId"
                        class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('categoryAccountId') border-red-400 @enderror">
                        <option value="">— Pilih Akun Pendapatan —</option>
                        @foreach ($revenueAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} | {{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryAccountId')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 text-white hover:bg-brand-700 shadow-sm active:scale-95 transition-all disabled:opacity-60"
                        wire:loading.attr="disabled" wire:target="createCategory">
                        <span wire:loading.remove wire:target="createCategory" class="flex items-center gap-2">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Simpan Kategori
                        </span>
                        <span wire:loading wire:target="createCategory">Menyimpan...</span>
                    </button>
                    <button type="button" wire:click="$toggle('showCreateForm')"
                        class="px-4 py-2.5 rounded-xl text-sm font-semibold text-zinc-600 hover:bg-zinc-100 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Existing category list (price editing) --}}
    <div>
        <h2 class="text-base font-semibold text-zinc-700 mb-3 flex items-center gap-2">
            <svg class="size-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
            </svg>
            Edit Harga Kategori
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $editableCategories = $categories->filter(fn($c) => $c->type->value !== 'bebas');
                $bebasCategories = $categories->filter(fn($c) => $c->type->value === 'bebas');
            @endphp

            @forelse ($editableCategories as $category)
                <div class="group bg-white p-5 rounded-2xl shadow-sm border border-brand-100 flex flex-col gap-4">
                    <div class="flex justify-between items-start gap-4">
                        <h3 class="text-base font-semibold text-zinc-900">{{ $category->name }}</h3>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="editCategory({{ $category->id }})" style="background-color:#fbbf24;color:#1c1917;" title="Edit Kategori"
                                class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                    <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                    <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                </svg>
                            </button>
<flux:button wire:click="confirmDelete({{ $category->id }})" variant="danger" size="xs" icon="trash" title="Hapus" />
                        </div>
                    </div>

                    <form wire:submit.prevent="updatePrice({{ $category->id }})" class="flex flex-col gap-3 mt-auto">
                        <div>
                            <label for="price_{{ $category->id }}"
                                class="block text-sm font-medium text-zinc-700 mb-1">
                                {{ $category->type->value === 'tahunan' ? 'Harga per Tahun' : 'Harga Satuan' }}
                            </label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-zinc-500 font-medium text-lg sm:text-xl">Rp</span>
                                </div>
                                <input type="text" inputmode="numeric" data-rupiah id="price_{{ $category->id }}"
                                    wire:model="prices.{{ $category->id }}"
                                    class="pl-14 py-3 sm:py-4 block w-full rounded-2xl border-2 border-zinc-200 bg-zinc-50/50 text-zinc-900 text-lg sm:text-xl font-bold focus:outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:bg-white transition-all shadow-sm @error('prices.' . $category->id) border-red-400 text-red-900 @enderror"
                                    placeholder="0" required>
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
                            wire:loading.attr="disabled" wire:target="updatePrice({{ $category->id }})">
                            <span wire:loading.remove wire:target="updatePrice({{ $category->id }})">Update Harga</span>
                            <span wire:loading wire:target="updatePrice({{ $category->id }})">Menyimpan...</span>
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

    {{-- Free-type categories (read-only, info only) --}}
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
                    <div class="group bg-zinc-50 border border-zinc-200 rounded-xl px-4 py-3 flex justify-between items-start gap-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-sm font-semibold text-zinc-700">{{ $category->name }}</span>
                            <span class="text-xs text-zinc-400">Bebas — nominal diisi tiap transaksi</span>
                        </div>
                        <div class="flex items-center shrink-0 gap-1.5">
                            <button type="button" wire:click="editCategory({{ $category->id }})" style="background-color:#fbbf24;color:#1c1917;" title="Edit Kategori"
                                class="inline-flex items-center justify-center size-7 rounded-lg transition-colors hover:opacity-90">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-3.5">
                                    <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.261-4.263a1.75 1.75 0 0 0 0-2.474Z" />
                                    <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V9A.75.75 0 0 1 13 9v2.25A2.75 2.75 0 0 1 10.25 14h-6.5A2.75 2.75 0 0 1 1 11.25v-6.5A2.75 2.75 0 0 1 3.75 2H6a.75.75 0 0 1 0 1.5H3.75Z" />
                                </svg>
                            </button>
<flux:button wire:click="confirmDelete({{ $category->id }})" variant="danger" size="xs" icon="trash" title="Hapus" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Edit category modal --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm" aria-modal="true">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-zinc-900">Edit Kategori Pendapatan</h3>
                    <button type="button" wire:click="$set('showEditModal', false)" class="text-zinc-400 hover:text-zinc-600 transition-colors">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveCategoryEdit" class="p-5 flex flex-col gap-4">
                    {{-- Category name --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-700">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="editCategoryName" placeholder="cth: Sewa Pelampung..."
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('editCategoryName') border-red-400 @enderror">
                        @error('editCategoryName') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category type --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-700">Tipe Kategori <span class="text-red-500">*</span></label>
                        <select wire:model="editCategoryType"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('editCategoryType') border-red-400 @enderror">
                            <option value="harga_x_qty">Harga × Jumlah (tiket, parkir, sewa per item)</option>
                            <option value="tahunan">Tahunan (sewa kios, kontrak per tahun)</option>
                            <option value="bebas">Bebas (nominal diisi manual saat transaksi)</option>
                        </select>
                        @error('editCategoryType') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="text-xs text-amber-600 mt-1">
                            <svg class="size-3.5 inline-block mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Mengubah tipe akan memengaruhi form input transaksi baru. Harga tidak akan terhapus namun bisa jadi tidak berlaku jika diubah ke Bebas.
                        </p>
                    </div>

                    {{-- Income account --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-semibold text-zinc-700">Masuk ke Akun <span class="text-red-500">*</span></label>
                        <select wire:model="editCategoryAccountId"
                            class="w-full rounded-xl border-2 border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-900 bg-white focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10 focus:outline-none transition-colors @error('editCategoryAccountId') border-red-400 @enderror">
                            <option value="">— Pilih Akun Pendapatan —</option>
                            @foreach ($revenueAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} | {{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('editCategoryAccountId') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-zinc-100">
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-2 text-sm font-medium text-zinc-600 hover:text-zinc-900 bg-zinc-100 hover:bg-zinc-200 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-sm transition-colors flex items-center gap-2" wire:loading.attr="disabled" wire:target="saveCategoryEdit">
                            <span wire:loading.remove wire:target="saveCategoryEdit">Simpan Perubahan</span>
                            <span wire:loading wire:target="saveCategoryEdit">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete category confirmation modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/50 backdrop-blur-sm" aria-modal="true">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-5 flex flex-col items-center text-center gap-3">
                    <div class="size-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-1">
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-zinc-900">Hapus Kategori?</h3>
                    <p class="text-sm text-zinc-500 leading-relaxed">
                        Yakin ingin menghapus kategori <span class="font-bold text-zinc-700">"{{ $deleteCategoryName }}"</span>?
                        <br><br>Data transaksi lama tidak akan hilang, namun kategori tidak akan bisa dihapus jika sudah pernah digunakan dalam transaksi.
                    </p>
                </div>
                
                <div class="p-4 bg-zinc-50 border-t border-zinc-100 flex justify-center gap-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)" class="px-5 py-2.5 text-sm font-medium text-zinc-700 hover:text-zinc-900 bg-white border border-zinc-200 hover:bg-zinc-50 rounded-xl transition-colors shadow-sm">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteCategory" class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl shadow-sm transition-colors flex items-center gap-2" wire:loading.attr="disabled" wire:target="deleteCategory">
                        <span wire:loading.remove wire:target="deleteCategory">Ya, Hapus</span>
                        <span wire:loading wire:target="deleteCategory">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
