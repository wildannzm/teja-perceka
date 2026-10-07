<x-layouts::auth.split :title="__('Masuk')">
    <div class="flex flex-col gap-5">
        <div class="text-center md:text-left">
            <h1 class="text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl">Masuk ke Akun Anda</h1>
            <p class="mt-1 text-sm text-zinc-500">Masukkan email dan password untuk melanjutkan</p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-center text-sm text-brand-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-4" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="email" class="text-sm font-medium text-zinc-700">Alamat Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    autocomplete="email webauthn" placeholder="email@example.com"
                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-base text-zinc-900 placeholder-zinc-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('email') border-red-400 bg-red-50 @enderror">
                @error('email')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-0.5" x-data="{ show: false }">
                <label for="password" class="text-sm font-medium text-zinc-700">Kata Sandi</label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-zinc-200 bg-zinc-50 py-2.5 pl-3.5 pr-11 text-base text-zinc-900 placeholder-zinc-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('password') border-red-400 bg-red-50 @enderror">
                    <button type="button" @click="show = !show" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-zinc-400 transition hover:text-zinc-600 focus:outline-none">
                        <svg x-show="!show" class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="show" x-cloak class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember" class="group flex cursor-pointer items-center gap-2.5 select-none">
                <span class="relative flex size-5 shrink-0 items-center justify-center">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                        class="peer size-5 cursor-pointer appearance-none rounded-md border border-zinc-300 bg-white shadow-sm transition hover:border-brand-400 checked:border-brand-600 checked:bg-brand-600 focus:outline-none focus:ring-4 focus:ring-brand-500/20">
                    <svg class="pointer-events-none absolute size-3 text-white opacity-0 transition peer-checked:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                <span class="text-sm text-zinc-600 transition group-hover:text-zinc-900">Ingat saya</span>
            </label>

            <button type="submit" data-test="login-button" :disabled="submitting"
                class="mt-1 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-500/30 active:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-75">
                <span x-show="!submitting">Masuk</span>
                <span x-show="submitting" x-cloak>Memproses...</span>
            </button>
        </form>

        <div x-data="passkeyLogin()" x-init="init()" class="flex flex-col gap-2">
            <div class="flex items-center gap-3 text-xs text-zinc-400">
                <span class="h-px flex-1 bg-zinc-200"></span>
                <span>{{ __('atau') }}</span>
                <span class="h-px flex-1 bg-zinc-200"></span>
            </div>
            <button type="button" @click="login()" :disabled="busy || !supported" data-test="passkey-login-button"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-3 text-base font-semibold text-zinc-700 transition hover:bg-zinc-50 focus:outline-none focus:ring-4 focus:ring-brand-500/15 disabled:cursor-not-allowed disabled:opacity-60">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                </svg>
                <span x-show="!busy">Masuk dengan passkey</span>
                <span x-show="busy" x-cloak>Memverifikasi...</span>
            </button>
            <p x-show="!supported" x-cloak class="text-center text-xs text-zinc-500">Browser ini belum mendukung passkey.</p>
            <p x-show="error" x-cloak x-text="error" class="text-center text-xs text-red-600"></p>
            <p class="text-center text-xs text-zinc-500">Bisa pakai fingerprint, Face ID, atau PIN perangkat.</p>
        </div>

        <script>
        function passkeyLogin() {
            return {
                busy: false,
                error: '',
                supported: true,
                init() {
                    this.checkSupport();
                    if (!window.Passkeys) {
                        window.addEventListener('passkeys:ready', () => this.checkSupport(), { once: true });
                        return;
                    }
                    this.startAutofill();
                },
                checkSupport() {
                    this.supported = window.Passkeys ? window.Passkeys.isSupported() : false;
                    if (this.supported) {
                        this.startAutofill();
                    }
                },
                startAutofill() {
                    window.Passkeys.autofill()
                        .then((response) => {
                            if (response && response.redirect) {
                                window.location.href = response.redirect;
                            }
                        })
                        .catch(() => {});
                },
                async login() {
                    if (this.busy) {
                        return;
                    }
                    this.error = '';
                    if (!window.Passkeys || !window.Passkeys.isSupported()) {
                        this.supported = false;
                        return;
                    }
                    window.Passkeys.cancel();
                    this.busy = true;
                    try {
                        const response = await window.Passkeys.verify();
                        window.location.href = response.redirect ?? @json(route('dashboard', absolute: false));
                    } catch (e) {
                        if (e && (e.name === 'UserCancelledError' || e.name === 'NotAllowedError')) {
                            return;
                        }
                        this.error = passkeyLoginErrorMessage(e);
                    } finally {
                        this.busy = false;
                    }
                },
            };
        }
        function passkeyLoginErrorMessage(error) {
            const name = error && error.name ? error.name : '';
            const raw = error && error.message ? String(error.message) : '';
            if (/already pending/i.test(raw)) {
                return 'Jendela verifikasi sebelumnya masih terbuka. Tutup dulu jendela itu, lalu klik lagi.';
            }
            if (name === 'NotSupportedError') {
                return 'Browser ini belum mendukung passkey.';
            }
            if (name === 'InvalidDomainError' || /invalid domain/i.test(raw)) {
                return 'Passkey tidak bisa dipakai di alamat ini. Untuk pengembangan lokal, buka lewat localhost.';
            }
            if (/expired/i.test(raw)) {
                return 'Sesi verifikasi kedaluwarsa. Muat ulang halaman ini, lalu coba lagi.';
            }
            if (/not recognized|removed from your account/i.test(raw)) {
                return 'Passkey tidak dikenali. Mungkin sudah dihapus dari akun Anda.';
            }
            if (/Unable to sign in with this account/i.test(raw)) {
                return 'Tidak bisa masuk dengan akun ini.';
            }
            return 'Gagal masuk dengan passkey. Coba lagi.';
        }
        </script>

    @if ($errors->any())
        @php $loginErrorField = $errors->has('password') ? 'Kata Sandi' : 'Email'; @endphp
        <script id="login-swal">
            (function waitForSwal(attempts) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: @json('Kesalahan pada '.$loginErrorField),
                        text: @json($errors->first()),
                        confirmButtonText: 'Coba Lagi',
                        confirmButtonColor: '#f59e0b',
                        customClass: {
                            popup: 'rounded-2xl shadow-2xl font-sans',
                            title: 'text-zinc-900 font-semibold',
                            htmlContainer: 'text-zinc-600',
                        },
                    });
                    return;
                }
                if (attempts > 0) {
                    setTimeout(() => waitForSwal(attempts - 1), 100);
                }
            })(120);
        </script>
    @endif
</x-layouts::auth.split>
