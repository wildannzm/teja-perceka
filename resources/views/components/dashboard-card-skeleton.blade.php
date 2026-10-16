@props([
    'cardClass' => 'bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm flex items-start gap-4',
    'valueClass' => 'mt-1 h-8 w-40 max-w-full rounded-md bg-zinc-200',
])

<div class="{{ $cardClass }} animate-pulse" aria-hidden="true">
    <div class="size-12 rounded-xl bg-zinc-200 shrink-0"></div>
    <div class="flex flex-col flex-1 min-w-0">
        <div class="h-5 w-28 rounded-md bg-zinc-200"></div>
        <div class="{{ $valueClass }}"></div>
    </div>
</div>
