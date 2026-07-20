<x-layouts::app :title="__('Dashboard Laporan')">
 <div class="flex h-full w-full flex-1 flex-col gap-4 p-4 sm:p-6">

 <div class="rounded-xl border border-brand-100 bg-white p-5 sm:p-8">
 <div class="flex items-center gap-3 mb-3">
 <span class="inline-block h-10 w-1.5 rounded-full bg-brand-700 shrink-0"></span>
 <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 leading-tight">
 Dashboard Laporan
 </h1>
 </div>
 <p class="text-sm sm:text-base text-zinc-500 leading-relaxed">
 Selamat datang di sistem pembukuan digital BUMDes Teja Perceka. Halaman ini akan menampilkan laporan keuangan dan rekap untuk Kepala Desa dan Pengawas.
 </p>
 </div>

 <div class="flex-1 rounded-xl border border-dashed border-brand-200 bg-brand-50/50 flex flex-col items-center justify-center gap-3 py-16 px-6 text-center">
 <svg class="size-10 text-brand-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
 </svg>
 <p class="text-sm text-brand-400 font-medium">
 Laporan keuangan & rekap untuk Kepala Desa & Pengawas akan ditampilkan di sini (Modul 5)
 </p>
 </div>

 </div>
</x-layouts::app>
