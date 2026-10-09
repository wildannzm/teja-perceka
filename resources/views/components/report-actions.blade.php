@props(['previewUrl', 'card' => true, 'sticky' => false])

{{-- Unified report actions: full-width print button below the report. --}}
<div>
    @if($sticky)
        <div class="h-[104px] sm:hidden" aria-hidden="true"></div>
    @endif
    <div class="{{ $sticky ? 'fixed bottom-0 left-0 right-0 z-20 sm:static' : '' }}">
        <div class="flex flex-col gap-2 {{ $card && !$sticky ? 'bg-white rounded-2xl border border-brand-100 shadow-sm p-4 sm:p-5 ' : '' }}{{ $sticky ? 'bg-white border-t border-brand-100 px-4 pt-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-8px_32px_-8px_rgba(0,0,0,0.1)] sm:rounded-2xl sm:border sm:shadow-sm sm:p-5 sm:pb-5' : '' }}">
            <a href="{{ $previewUrl }}" target="_blank" rel="noopener"
                class="inline-flex items-center justify-center gap-2.5 w-full min-h-[56px] px-4 text-base bg-brand-600 text-white hover:bg-brand-700 font-semibold rounded-2xl transition-colors shadow-md">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Cetak
            </a>
        </div>
    </div>
</div>
