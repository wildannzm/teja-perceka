<div x-data="{ editingUserId: @entangle('editingUserId') }">
 <div class="flex h-full w-full flex-col gap-6">
 <div class="flex items-center justify-between">
 <h1 class="text-2xl font-semibold text-zinc-900">Kelola Hak Akses</h1>
 </div>

 <!-- Search and List -->
 <div class="flex flex-col gap-4 bg-white p-6 rounded-2xl shadow-sm border border-brand-100">
 
 <div class="flex justify-between items-center mb-6">
 <div class="w-full md:w-1/2 relative">
 <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
 <svg class="size-6 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
 </svg>
 </div>
 <input 
 type="text" 
 wire:model.live.debounce.300ms="search" 
 placeholder="Cari nama atau email pengguna..." 
 class="pl-12 block w-full rounded-2xl border-2 border-zinc-200 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-base py-3"
 >
 </div>
 </div>

 <div class="overflow-x-auto rounded-xl border border-zinc-200">
 <table class="w-full text-sm text-left text-zinc-600">
 <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 border-b border-zinc-200">
 <tr>
 <th scope="col" class="px-6 py-3">Nama Lengkap</th>
 <th scope="col" class="px-6 py-3">Email</th>
 <th scope="col" class="px-6 py-3">Hak Akses</th>
 <th scope="col" class="px-6 py-3 text-right">Aksi</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-zinc-100">
 @forelse($users as $user)
 <tr class="bg-white hover:bg-zinc-50 transition-colors">
 <td class="px-6 py-4 font-medium text-zinc-900">
 {{ $user->name }}
 </td>
 <td class="px-6 py-4">
 {{ $user->email }}
 </td>
 <td class="px-6 py-4">
 <span class="px-2.5 py-1 text-xs font-medium bg-brand-50 text-brand-700 rounded-full border border-brand-200">
 {{ str($user->roles->first()?->name ?? 'User')->replace('_', ' ')->title() }}
 </span>
 </td>
 <td class="px-6 py-4 text-right">
 <button 
 wire:click="editUser({{ $user->id }})" 
 class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors border border-transparent focus:outline-none focus:ring-2 focus:ring-0 focus:ring-offset-2"
 >
 Edit Profil
 </button>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="4" class="px-6 py-8 text-center text-zinc-500">
 <svg class="size-10 text-zinc-400 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
 </svg>
 Tidak ada data pengguna ditemukan.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <div class="mt-4">
 {{ $users->links() }}
 </div>
 </div>
 </div>

 <!-- Edit Modal (Pure Tailwind) -->
 <div x-show="editingUserId !== null" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
 <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
 <!-- Overlay -->
 <div 
 x-show="editingUserId !== null" 
 x-transition:enter="ease-out duration-300"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="ease-in duration-200"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 class="fixed inset-0 transition-opacity bg-zinc-900/50 backdrop-blur-sm" 
 aria-hidden="true" 
 @click="editingUserId = null; $wire.cancelEdit()"
 ></div>

 <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

 <!-- Modal panel -->
 <div 
 x-show="editingUserId !== null"
 x-transition:enter="ease-out duration-300"
 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave="ease-in duration-200"
 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
 class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl relative z-10"
 >
 <h2 class="text-xl font-bold text-zinc-900 mb-6" id="modal-title">Edit Profil Pengguna</h2>
 
 <form wire:submit.prevent="saveUser" class="flex flex-col gap-5">
 
 <div class="flex flex-col gap-1.5">
 <label for="name" class="text-sm font-medium text-zinc-700">Nama Lengkap</label>
 <input 
 id="name" 
 type="text" 
 wire:model="name" 
 placeholder="Masukkan nama..." 
 required
 class="w-full rounded-xl border-zinc-300 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-sm @error('name') border-red-400 @enderror"
 >
 @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
 </div>

 <div class="flex flex-col gap-1.5">
 <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
 <input 
 id="email" 
 type="email" 
 wire:model="email" 
 placeholder="email@contoh.com" 
 required
 class="w-full rounded-xl border-zinc-300 text-zinc-900 focus:border-brand-500 focus:ring-0 transition-colors shadow-sm text-sm @error('email') border-red-400 @enderror"
 >
 @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
 </div>
 
 <div class="flex justify-end gap-3 mt-4">
 <button 
 type="button" 
 wire:click="cancelEdit" 
 class="px-4 py-2.5 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-xl hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-0 transition-colors"
 >
 Batal
 </button>
 <button 
 type="submit" 
 class="px-4 py-2.5 text-sm font-medium text-white bg-brand-500 border border-transparent rounded-xl hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-0 transition-colors flex items-center gap-2"
 wire:loading.attr="disabled"
 >
 <span wire:loading.remove wire:target="saveUser">Simpan Perubahan</span>
 <span wire:loading wire:target="saveUser" class="flex items-center gap-2">
 <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 Menyimpan...
 </span>
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>
