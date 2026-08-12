<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Pengaturan Profil') }}</flux:heading>

    <x-settings.layout :heading="__('Profil')" :subheading="__('Perbarui nama dan alamat email Anda')">
        <div class="my-6 w-full space-y-6">
            <div class="flex flex-col gap-2 p-5 bg-white border border-zinc-200 rounded-xl shadow-sm">
                <div class="text-sm font-medium text-zinc-500">Nama Lengkap</div>
                <div class="text-lg font-semibold text-brand-900">{{ auth()->user()->name }}</div>
            </div>

            <div class="flex flex-col gap-2 p-5 bg-white border border-zinc-200 rounded-xl shadow-sm">
                <div class="text-sm font-medium text-zinc-500">Alamat Email</div>
                <div class="text-lg font-semibold text-brand-900 break-all">{{ auth()->user()->email }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-2 p-5 bg-brand-50 border border-brand-100 rounded-xl shadow-sm">
                    <div class="text-sm font-medium text-brand-700">Hak Akses</div>
                    <div class="text-base font-semibold text-brand-900 uppercase">
                        {{ str(auth()->user()->roles->first()?->name ?? 'User')->replace('_', ' ')->title() }}
                    </div>
                </div>

                @if (auth()->user()->unitWisata)
                    <div class="flex flex-col gap-2 p-5 bg-brand-50 border border-brand-100 rounded-xl shadow-sm">
                        <div class="text-sm font-medium text-brand-700">Unit Usaha</div>
                        <div class="text-base font-semibold text-brand-900">
                            {{ auth()->user()->unitWisata->nama }}
                        </div>
                    </div>
                @endif
            </div>

            <div
                class="p-4 bg-zinc-50 rounded-xl border border-zinc-200 text-sm text-zinc-600 mt-6 flex items-start gap-3">
                <svg class="size-5 text-zinc-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <p>Data profil di atas hanya dapat diubah oleh administrator atau direktur terkait. Anda tidak diizinkan
                    mengubah identitas dasar secara mandiri. Silakan ke menu "Security" untuk mengganti password.</p>
            </div>
        </div>

    </x-settings.layout>
</section>
