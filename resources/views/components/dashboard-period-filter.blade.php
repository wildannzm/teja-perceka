@props(['period' => 'bulanan', 'periodLabel' => '', 'periodRange' => ''])

<div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-3 sm:p-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="grid grid-cols-2 gap-1.5 sm:flex sm:items-center sm:gap-1 bg-zinc-100 rounded-2xl sm:rounded-full p-1.5" role="group" aria-label="Filter periode rekap">
        @foreach (['mingguan' => 'Mingguan', 'bulanan' => 'Bulanan', 'semester' => 'Semester', 'tahunan' => 'Tahunan'] as $key => $label)
            <button type="button" wire:click="$set('period', '{{ $key }}')" aria-pressed="{{ $period === $key ? 'true' : 'false' }}"
                class="px-3 py-2.5 sm:py-2 text-sm font-semibold rounded-xl sm:rounded-full whitespace-nowrap transition-all active:scale-[.98] {{ $period === $key ? 'bg-white text-brand-700 shadow ring-1 ring-black/5' : 'text-zinc-500 hover:text-zinc-900' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="flex items-center gap-2.5 min-w-0 px-1 sm:px-0">
        <span class="size-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
        </span>
        <p class="text-sm text-zinc-500 truncate">Periode: <span class="font-semibold text-zinc-800">{{ $periodLabel }} &middot; {{ $periodRange }}</span></p>
    </div>
</div>
