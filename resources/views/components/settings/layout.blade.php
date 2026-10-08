<div class="flex items-start max-md:flex-col gap-8">
    <div class="w-full pb-4 md:w-[220px] shrink-0" wire:ignore>
        <nav aria-label="{{ __('Pengaturan') }}" class="flex flex-col gap-1">
            <a href="{{ route('profile.edit') }}" wire:navigate
                class="px-3 py-2.5 text-sm font-semibold rounded-xl transition-all text-center {{ request()->routeIs('profile.edit') ? 'bg-brand-600 text-white shadow-md' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900' }}">
                {{ __('Profil') }}
            </a>
            <a href="{{ route('security.edit') }}" wire:navigate
                class="px-3 py-2.5 text-sm font-semibold rounded-xl transition-all text-center {{ request()->routeIs('security.edit') ? 'bg-brand-600 text-white shadow-md' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-900' }}">
                {{ __('Keamanan') }}
            </a>
        </nav>
    </div>

    <hr class="w-full border-zinc-200 md:hidden my-4" />

    <div class="flex-1 self-stretch max-md:pt-2">
        @if (isset($heading))
            <h2 class="text-xl font-semibold text-zinc-900">{{ $heading }}</h2>
        @endif
        @if (isset($subheading))
            <p class="text-sm text-zinc-500 mt-1">{{ $subheading }}</p>
        @endif

        <div class="mt-5 w-full max-w-2xl">
            {{ $slot }}
        </div>
    </div>
</div>
