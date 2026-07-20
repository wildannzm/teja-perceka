<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
 <head>
 @include('partials.head')
 </head>
 <body class="min-h-screen bg-[#FDFDFC] pb-[env(safe-area-inset-bottom)] relative overflow-x-hidden">
 <!-- Dekorasi Background Lingkaran Solid (Reference Palettemaker) -->
 <div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden">
 <!-- Lingkaran Besar Kiri Atas -->
 <div class="absolute top-[-20%] left-[-10%] w-[400px] h-[400px] md:w-[600px] md:h-[600px] rounded-full bg-brand-300 opacity-20"></div>
 <!-- Lingkaran Menengah (Overlap) -->
 <div class="absolute top-[-10%] left-[10%] w-[250px] h-[250px] md:w-[400px] md:h-[400px] rounded-full bg-brand-200 opacity-30"></div>
 <!-- Lingkaran Pojok Kanan Bawah -->
 <div class="absolute bottom-[-30%] right-[-10%] w-[400px] h-[400px] md:w-[700px] md:h-[700px] rounded-full bg-brand-400 opacity-[0.15]"></div>
 </div>
 <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white">
 <flux:sidebar.header>
 <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
 <flux:sidebar.collapse class="lg:hidden" />
 </flux:sidebar.header>

 <flux:sidebar.nav>
 <!-- Dashboard Umum Dihapus Sesuai Permintaan -->

 @role('kepala_desa')
 <flux:sidebar.group :heading="__('Kepala Desa')" class="grid">
 <flux:sidebar.item icon="chart-bar" :href="route('kepala-desa.dashboard')" :current="request()->routeIs('kepala-desa.dashboard')" wire:navigate>
 {{ __('Rekap Unit') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="printer" :href="route('kepala-desa.report')" :current="request()->routeIs('kepala-desa.report')" wire:navigate>
 {{ __('Cetak Laporan') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="users" :href="route('kepala-desa.users')" :current="request()->routeIs('kepala-desa.users')" wire:navigate>
 {{ __('Kelola Profil Akun') }}
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole

 @role('sekretaris')
 <flux:sidebar.group :heading="__('Sekretaris')" class="grid">
 <flux:sidebar.item icon="chart-bar" :href="route('sekretaris.dashboard')" :current="request()->routeIs('sekretaris.dashboard')" wire:navigate>
 {{ __('Rekap Unit') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="table-cells" :href="route('sekretaris.transaksi')" :current="request()->routeIs('sekretaris.transaksi')" wire:navigate>
 {{ __('Kelola Jurnal') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="printer" :href="route('sekretaris.laporan')" :current="request()->routeIs('sekretaris.laporan')" wire:navigate>
 {{ __('Cetak Laporan') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="minus-circle" :href="route('pengeluaran.catat')" :current="request()->routeIs('pengeluaran.catat')" wire:navigate>
 {{ __('Catat Pengeluaran') }}
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole

 @role('bendahara')
 <flux:sidebar.group :heading="__('Bendahara')" class="grid">
 <flux:sidebar.item icon="chart-bar" :href="route('bendahara.dashboard')" :current="request()->routeIs('bendahara.dashboard')" wire:navigate>
 {{ __('Rekap Keuangan') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="table-cells" :href="route('bendahara.transaksi')" :current="request()->routeIs('bendahara.transaksi')" wire:navigate>
 {{ __('Kelola Jurnal') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="printer" :href="route('bendahara.laporan')" :current="request()->routeIs('bendahara.laporan')" wire:navigate>
 {{ __('Cetak Laporan') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="minus-circle" :href="route('pengeluaran.catat')" :current="request()->routeIs('pengeluaran.catat')" wire:navigate>
 {{ __('Catat Pengeluaran') }}
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole

 @role('direktur_bumdes')
 <flux:sidebar.group :heading="__('Direktur BUMDes')" class="grid">
 <flux:sidebar.item icon="chart-bar" :href="route('direktur-bumdes.dashboard')" :current="request()->routeIs('direktur-bumdes.dashboard')" wire:navigate>
 {{ __('Dashboard & Rekap') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="table-cells" :href="route('direktur-bumdes.transaksi')" :current="request()->routeIs('direktur-bumdes.transaksi')" wire:navigate>
 {{ __('Kelola Jurnal') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="printer" :href="route('direktur-bumdes.laporan')" :current="request()->routeIs('direktur-bumdes.laporan')" wire:navigate>
 {{ __('Cetak Laporan') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="minus-circle" :href="route('pengeluaran.catat')" :current="request()->routeIs('pengeluaran.catat')" wire:navigate>
 {{ __('Catat Pengeluaran') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="users" :href="route('direktur-bumdes.kelola-akun')" :current="request()->routeIs('direktur-bumdes.kelola-akun')" wire:navigate>
 {{ __('Kelola Akun Unit') }}
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole

 @role('kepala_unit')
 <flux:sidebar.group :heading="__('Unit Wisata')" class="grid">
 <flux:sidebar.item icon="home" :href="route('dashboard.unit')" :current="request()->routeIs('dashboard.unit')" wire:navigate>
 Dashboard
 </flux:sidebar.item>
 <flux:sidebar.item icon="pencil-square" :href="route('unit.input-transaksi')" :current="request()->routeIs('unit.input-transaksi')" wire:navigate>
 Input Transaksi
 </flux:sidebar.item>
 <flux:sidebar.item icon="document-chart-bar" :href="route('unit.riwayat-transaksi')" :current="request()->routeIs('unit.riwayat-transaksi')" wire:navigate>
 Riwayat & Rekap
 </flux:sidebar.item>
 <flux:sidebar.item icon="currency-dollar" :href="route('unit.kelola-harga')" :current="request()->routeIs('unit.kelola-harga')" wire:navigate>
 Kelola Harga
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole

 @role('pengawas')
 <flux:sidebar.group :heading="__('Pengawas')" class="grid">
 <flux:sidebar.item icon="chart-bar" :href="route('pengawas.dashboard')" :current="request()->routeIs('pengawas.dashboard')" wire:navigate>
 {{ __('Dashboard & Rekap') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="table-cells" :href="route('pengawas.transaksi')" :current="request()->routeIs('pengawas.transaksi')" wire:navigate>
 {{ __('Lihat Jurnal') }}
 </flux:sidebar.item>
 <flux:sidebar.item icon="printer" :href="route('pengawas.laporan')" :current="request()->routeIs('pengawas.laporan')" wire:navigate>
 {{ __('Cetak Laporan') }}
 </flux:sidebar.item>
 </flux:sidebar.group>
 @endrole
 </flux:sidebar.nav>

 <flux:spacer />

 <flux:sidebar.nav>
 {{-- Link dev tools dihapus, akan diisi menu navigasi BUMDes di modul berikutnya --}}
 </flux:sidebar.nav>

 <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
 </flux:sidebar>

 <!-- Mobile header — tampil di hp, tersembunyi di desktop -->
 <flux:header class="lg:hidden border-b border-zinc-200 bg-white h-14">
 <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

 <flux:spacer />

 <flux:dropdown position="top" allign="end">
 <flux:profile
 :initials="auth()->user()->initials()"
 icon-trailing="chevron-down"
 />

 <flux:menu>
 <flux:menu.radio.group>
 <div class="p-0 text-sm font-normal">
 <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
 <flux:avatar
 :name="auth()->user()->name"
 :initials="auth()->user()->initials()"
 />

 <div class="grid flex-1 text-start text-sm leading-tight">
 <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
 <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
 </div>
 </div>
 </div>
 </flux:menu.radio.group>

 <flux:menu.separator />

 <flux:menu.radio.group>
 <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
 {{ __('Settings') }}
 </flux:menu.item>
 </flux:menu.radio.group>

 <flux:menu.separator />

 <form method="POST" action="{{ route('logout') }}" class="w-full">
 @csrf
 <flux:menu.item
 as="button"
 type="submit"
 icon="arrow-right-start-on-rectangle"
 class="w-full cursor-pointer"
 data-test="logout-button"
 >
 {{ __('Log out') }}
 </flux:menu.item>
 </form>
 </flux:menu>
 </flux:dropdown>
 </flux:header>

 {{ $slot }}

 @persist('toast')
 <flux:toast.group>
 <flux:toast />
 </flux:toast.group>
 @endpersist

 @fluxScripts
 </body>
</html>