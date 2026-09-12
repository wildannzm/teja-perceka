@props(['title', 'description' => null, 'accent' => 'bg-brand-400'])

<div class="rounded-xl border border-brand-100 bg-white p-5 sm:p-6 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="inline-block h-10 w-1.5 rounded-full {{ $accent }} shrink-0"></span>
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-semibold text-brand-900 leading-tight">{{ $title }}</h1>
                @if ($description)
                    <p class="text-sm text-zinc-500 mt-0.5 leading-relaxed">{{ $description }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 shrink-0 w-full sm:w-auto">{{ $actions }}</div>
        @endisset
    </div>
</div>
