<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[#FDFDFC] antialiased relative overflow-x-hidden">
    <!-- Solid circle background decoration (Palettemaker reference) -->
    <div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden">
        <!-- Large circle 1 -->
        <div
            class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] md:w-[700px] md:h-[700px] rounded-full bg-brand-300 opacity-20">
        </div>
        <!-- Medium circle (overlap) -->
        <div
            class="absolute top-[-10%] left-[15%] w-[300px] h-[300px] md:w-[400px] md:h-[400px] rounded-full bg-brand-200 opacity-40">
        </div>
        <!-- Top-right circle -->
        <div
            class="absolute top-[-30%] right-[-10%] w-[400px] h-[400px] md:w-[600px] md:h-[600px] rounded-full bg-brand-400 opacity-20">
        </div>
    </div>
    <div class="flex min-h-dvh flex-col items-center justify-center gap-4 p-4 sm:gap-6 sm:p-6 md:p-10">
        <div class="flex w-full max-w-md flex-col gap-4">
            <a href="{{ route('home') }}" class="flex flex-col items-center font-medium" wire:navigate>
                <img src="{{ asset('assets/images/logo-sidebar-bumdes-teja-perceka.png') }}" alt="Logo BUMDes Teja Perceka" class="h-28 w-auto max-w-[320px] object-contain sm:h-32">
            </a>
            <div
                class="flex flex-col gap-6 bg-white rounded-2xl p-6 sm:p-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-brand-500">
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
