<div class="flex flex-col gap-6 max-w-4xl mx-auto w-full pb-20">
 <div class="flex flex-col gap-2">
 <h1 class="text-2xl font-bold text-zinc-900">Kelola Harga Kategori</h1>
 <p class="text-sm text-zinc-500">Atur harga untuk kategori transaksi di unit wisata Anda ({{ $unitNama }}).</p>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 @forelse ($categories as $category)
 <div class="bg-white p-5 rounded-2xl shadow-sm border border-brand-100 flex flex-col gap-4 relative">
 <div>
 <h3 class="text-lg font-semibold text-zinc-900">{{ $category->nama }}</h3>
 <div class="text-xs text-zinc-500 mt-1 uppercase tracking-wider font-medium">
 {{ $category->jenis->value }} &bull; Tipe: {{ $category->tipe->value }}
 </div>
 </div>

 <form wire:submit.prevent="updateHarga({{ $category->id }})" class="flex flex-col gap-3 mt-auto">
 <div>
 <label for="price_{{ $category->id }}" class="block text-sm font-medium text-zinc-700 mb-1">Harga Saat Ini / Baru</label>
 <div class="relative">
 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
 <span class="text-zinc-500 sm:text-sm">Rp</span>
 </div>
 <input 
 type="number" 
 id="price_{{ $category->id }}" 
 wire:model="prices.{{ $category->id }}" 
 class="pl-10 block w-full rounded-xl border-zinc-300 text-zinc-900 focus:border-brand-500 focus:ring-brand-500 transition-colors shadow-sm @error('prices.'.$category->id) border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
 placeholder="0" 
 required 
 min="1"
 >
 </div>
 @error('prices.'.$category->id)
 <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
 @enderror
 @if (session()->has('success_'.$category->id))
 <p class="mt-1 text-xs text-brand-600 flex items-center gap-1">
 <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
 {{ session('success_'.$category->id) }}
 </p>
 @endif
 </div>

 <button 
 type="submit" 
 class="w-full py-2.5 px-4 mt-2 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-zinc-900 bg-brand-300 hover:bg-brand-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-400 :ring-offset-zinc-900 transition-all active:scale-[0.98] disabled:opacity-70 flex justify-center items-center gap-2"
 wire:loading.attr="disabled"
 wire:target="updateHarga({{ $category->id }})"
 >
 <span wire:loading.remove wire:target="updateHarga({{ $category->id }})">Update Harga</span>
 <span wire:loading wire:target="updateHarga({{ $category->id }})" class="flex items-center gap-2">
 <svg class="animate-spin size-4 text-zinc-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 Menyimpan...
 </span>
 </button>
 </form>
 </div>
 @empty
 <div class="col-span-full bg-zinc-50 rounded-2xl border border-zinc-200 p-8 text-center flex flex-col items-center justify-center">
 <svg class="size-12 text-zinc-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 <p class="text-zinc-600 font-medium text-lg">Tidak ada kategori transaksi dengan harga tetap.</p>
 <p class="text-zinc-500 text-sm mt-1">Semua kategori di unit Anda bertipe bebas atau belum ada kategori sama sekali.</p>
 </div>
 @endforelse
 </div>
</div>
