<x-layouts::auth.split :title="__('Verifikasi Dua Faktor')">
    <div class="flex flex-col gap-5" x-data="twoFactorChallenge()" x-init="init(@js($errors->has('recovery_code')))">
        <div class="text-center">
            <span class="inline-flex size-11 items-center justify-center rounded-2xl bg-brand-100 text-brand-700">
                <svg class="size-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
            </span>
            <h1 class="mt-3 text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl" x-show="!showRecoveryInput">Kode Autentikasi</h1>
            <h1 class="mt-3 text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl" x-show="showRecoveryInput" x-cloak>Kode Pemulihan</h1>
            <p class="mt-1 text-sm text-zinc-500" x-show="!showRecoveryInput">Masukkan kode 6 digit dari aplikasi authenticator Anda.</p>
            <p class="mt-1 text-sm text-zinc-500" x-show="showRecoveryInput" x-cloak>Masukkan salah satu kode pemulihan darurat Anda.</p>
        </div>

            <form method="POST" action="{{ route('two-factor.login.store') }}" class="flex flex-col gap-4" x-ref="challengeForm" @submit="submitting = true">
                @csrf

                <div x-show="!showRecoveryInput">
                    <label for="code-0" class="sr-only">Kode autentikasi 6 digit</label>
                    <div class="flex items-center justify-center gap-2" @paste.prevent="handlePaste($event)">
                        <template x-for="(digit, index) in digits" :key="index">
                            <input :id="'code-' + index" type="text" inputmode="numeric" autocomplete="one-time-code"
                                maxlength="1" :value="digits[index]" :data-index="index"
                                @input="handleInput(index, $event)" @keydown="handleKeydown(index, $event)" @focus="$event.target.select()"
                                x-ref="otpInputs"
                                class="size-12 rounded-xl border border-zinc-200 bg-zinc-50 text-center text-xl font-bold text-zinc-900 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('code') border-red-400 bg-red-50 @enderror">
                        </template>
                    </div>
                    <input type="hidden" name="code" :value="codeValue">
                    <p class="mt-2 text-center text-xs text-zinc-500">Kode berubah setiap 30 detik. Tunggu kode baru jika kedaluwarsa.</p>
                    @error('code')
                        <p class="mt-1 text-center text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="showRecoveryInput" x-cloak>
                    <label for="recovery_code" class="text-sm font-medium text-zinc-700">Kode Pemulihan</label>
                    <input id="recovery_code" name="recovery_code" type="text" x-ref="recoveryInput"
                        x-model="recoveryValue" x-bind:required="showRecoveryInput" autocomplete="one-time-code"
                        placeholder="Contoh: abcdef-12345"
                        class="mt-1.5 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 py-2.5 text-center font-mono text-base text-zinc-900 placeholder-zinc-400 transition focus:border-brand-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-500/15 @error('recovery_code') border-red-400 bg-red-50 @enderror">
                    @error('recovery_code')
                        <p class="mt-1 text-center text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" :disabled="submitting || (!showRecoveryInput && codeValue.length < 6)"
                    class="mt-1 flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-3 text-base font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-brand-500/30 active:bg-brand-800 disabled:cursor-not-allowed disabled:opacity-75">
                    <span x-show="!submitting">Lanjutkan</span>
                    <span x-show="submitting" x-cloak>Memverifikasi...</span>
                </button>
            </form>

            <div class="mt-4 text-center text-sm">
                <span class="text-zinc-400">atau Anda bisa </span>
                <button type="button" @click="toggleInput()" class="font-medium text-brand-600 hover:text-brand-700 hover:underline">
                    <span x-show="!showRecoveryInput">masuk dengan kode pemulihan</span>
                    <span x-show="showRecoveryInput" x-cloak>masuk dengan kode autentikasi</span>
                </button>
            </div>
    </div>

    <script>
    function twoFactorChallenge() {
        return {
            showRecoveryInput: false,
            digits: ['', '', '', '', '', ''],
            recoveryValue: '',
            submitting: false,
            get codeValue() {
                return this.digits.join('');
            },
            init(hasRecoveryError) {
                this.showRecoveryInput = !!hasRecoveryError;
                this.$nextTick(() => this.focusCurrent());
            },
            focusCurrent() {
                if (this.showRecoveryInput) {
                    this.$refs.recoveryInput?.focus();
                    return;
                }
                const inputs = this.otpInputs();
                const firstEmpty = this.digits.findIndex((d) => !d);
                (inputs[firstEmpty === -1 ? 5 : firstEmpty] ?? inputs[0])?.focus();
            },
            otpInputs() {
                return Array.from(this.$root.querySelectorAll('input[id^="code-"]'));
            },
            handleInput(index, event) {
                const input = event.target;
                const value = (input.value ?? '').replace(/\D/g, '').slice(-1);
                this.digits[index] = value;
                input.value = value;
                if (value && index < 5) {
                    this.$nextTick(() => this.otpInputs()[index + 1]?.focus());
                }
                this.maybeSubmit();
            },
            maybeSubmit() {
                if (this.submitting || this.showRecoveryInput) {
                    return;
                }
                if (this.digits.every((d) => d)) {
                    this.submitting = true;
                    this.$nextTick(() => this.$refs.challengeForm?.requestSubmit());
                }
            },
            handleKeydown(index, event) {
                if (event.key === 'Backspace' && !this.digits[index] && index > 0) {
                    event.preventDefault();
                    this.digits[index - 1] = '';
                    const inputs = this.otpInputs();
                    if (inputs[index - 1]) {
                        inputs[index - 1].value = '';
                    }
                    this.$nextTick(() => this.otpInputs()[index - 1]?.focus());
                }
            },
            handlePaste(event) {
                const text = (event.clipboardData?.getData('text') ?? '').replace(/\D/g, '').slice(0, 6);
                if (!text) {
                    return;
                }
                this.digits = [...text.padEnd(6, '').slice(0, 6)];
                this.$nextTick(() => {
                    this.otpInputs()[Math.min(text.length, 5)]?.focus();
                });
                this.maybeSubmit();
            },
            toggleInput() {
                this.showRecoveryInput = !this.showRecoveryInput;
                this.digits = ['', '', '', '', '', ''];
                this.recoveryValue = '';
                this.$nextTick(() => this.focusCurrent());
            },
        };
    }
    </script>

    @if ($errors->has('code') || $errors->has('recovery_code'))
        <script id="two-factor-swal">
            (function waitForSwal(attempts) {
                if (window.Swal) {
                    window.Swal.fire({
                        icon: 'error',
                        title: @json('Kode salah'),
                        text: @json($errors->first('code') ?: $errors->first('recovery_code')),
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
