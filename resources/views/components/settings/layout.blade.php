<div class="flex items-start max-md:flex-col gap-8">
 <div class="w-full pb-4 md:w-[220px] shrink-0">
 <nav aria-label="{{ __('Pengaturan') }}" class="flex flex-col gap-1">
 <a href="{{ route('profile.edit') }}" wire:navigate class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('profile.edit') ? 'bg-brand-50 text-brand-900' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}">
 {{ __('Profil') }}
 </a>
 <a href="{{ route('security.edit') }}" wire:navigate class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('security.edit') ? 'bg-brand-50 text-brand-900' : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900' }}">
 {{ __('Kata Sandi') }}
 </a>
 </nav>
 </div>

 <hr class="w-full border-zinc-200 md:hidden my-4" />

 <div class="flex-1 self-stretch max-md:pt-2">
 @if(isset($heading))
 <h2 class="text-xl font-semibold text-zinc-900">{{ $heading }}</h2>
 @endif
 @if(isset($subheading))
 <p class="text-sm text-zinc-500 mt-1">{{ $subheading }}</p>
 @endif

 <div class="mt-5 w-full max-w-2xl">
 {{ $slot }}
 </div>
 </div>
</div>
