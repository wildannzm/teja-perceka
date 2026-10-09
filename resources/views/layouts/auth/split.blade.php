<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[#FDFDFC] antialiased">
    <div class="fixed inset-0 z-[-1] pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute top-[-20%] left-[-10%] size-[500px] rounded-full bg-brand-300 opacity-20 md:size-[700px]"></div>
        <div class="absolute top-[-10%] left-[15%] size-[300px] rounded-full bg-brand-200 opacity-40 md:size-[400px]"></div>
        <div class="absolute top-[-30%] right-[-10%] size-[400px] rounded-full bg-brand-400 opacity-20 md:size-[600px]"></div>
    </div>

    <div class="flex min-h-dvh items-center justify-center p-4 sm:p-6 md:p-10">
        <div class="grid w-full max-w-md overflow-hidden rounded-3xl border border-zinc-200/70 bg-white shadow-[0_1px_2px_rgb(16,24,40,0.05),0_16px_40px_-16px_rgb(16,24,40,0.12)] md:max-w-4xl md:grid-cols-2">
            <!-- Logo panel: top on mobile, left on md+ -->
            <div class="flex flex-col items-center justify-center gap-4 border-b border-zinc-100 bg-gradient-to-b from-brand-50/90 to-white p-6 sm:p-8 md:border-b-0 md:border-r md:p-10">
                <a href="{{ route('home') }}" wire:navigate>
                    <img src="{{ asset('assets/images/logo-sidebar-bumdes-teja-perceka.png') }}" alt="Logo BUMDes Teja Perceka"
                        class="h-28 w-auto max-w-[280px] object-contain sm:h-32 md:h-44 md:max-w-[320px] lg:h-52">
                </a>
                @php
                    $quotes = [
                        'Pelayanan yang tulus membangun kepercayaan yang abadi.',
                        'Integritas adalah modal utama dalam setiap amanah.',
                        'Desa maju dimulai dari kerja yang jujur dan konsisten.',
                        'Kepercayaan tumbuh dari transparansi dan tanggung jawab.',
                        'Langkah kecil yang disiplin mengantar pada hasil yang besar.',
                        'Melayani dengan hati, bekerja dengan akuntabilitas.',
                    ];
                    $quote = $quotes[array_rand($quotes)];
                @endphp
                <blockquote class="hidden max-w-xs text-center md:block">
                    <p class="text-sm italic leading-relaxed text-brand-900/80">&ldquo;{{ $quote }}&rdquo;</p>
                </blockquote>
            </div>

            <!-- Form panel -->
            <div class="flex flex-col justify-center gap-6 p-6 sm:p-8 md:p-10">
                {{ $slot }}
                <p class="text-center text-xs text-zinc-400">© {{ date('Y') }} BUMDes Teja Perceka</p>
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
