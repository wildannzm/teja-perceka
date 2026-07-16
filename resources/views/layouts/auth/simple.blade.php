<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-brand-950 antialiased">
        <div class="flex min-h-dvh flex-col items-center justify-center gap-4 p-4 sm:gap-6 sm:p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-3">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-12 w-12 mb-1 items-center justify-center rounded-xl bg-brand-700 shadow-lg">
                        <x-app-logo-icon class="size-8 fill-current text-white" />
                    </span>
                    <span class="text-base font-semibold text-white tracking-wide">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <div class="flex flex-col gap-5 bg-white rounded-2xl p-6 sm:p-8 shadow-2xl">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
