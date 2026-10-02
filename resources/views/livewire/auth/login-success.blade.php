<x-layouts::auth.split :title="__('Login Berhasil')">
    <div class="flex flex-col items-center gap-3 py-8 text-center">
        <span class="flex size-14 items-center justify-center rounded-full bg-emerald-100">
            <svg class="size-7 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </span>
        <h1 class="text-xl font-bold tracking-tight text-zinc-900 sm:text-2xl">Login Berhasil!</h1>
        <p class="text-sm text-zinc-500">Selamat datang kembali, {{ $name }}. Anda akan dialihkan ke dashboard. <a href="{{ $next }}" class="font-medium text-brand-600 hover:underline">Klik di sini jika tidak dialihkan</a></p>
    </div>

    <script id="login-success-swal">
        const loginNext = @json($next);
        const loginName = @json($name);
        function goNext() {
            window.location.href = loginNext;
        }
        (function waitForSwal(attempts) {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: 'Selamat datang kembali, ' + loginName + '!',
                    timer: 2500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl font-sans',
                        title: 'text-zinc-900 font-semibold',
                        htmlContainer: 'text-zinc-600',
                        timerProgressBar: 'bg-amber-400',
                    },
                }).then(goNext);
                setTimeout(goNext, 3000);
                return;
            }
            if (attempts > 0) {
                setTimeout(() => waitForSwal(attempts - 1), 100);
            } else {
                goNext();
            }
        })(120);
    </script>
</x-layouts::auth.split>
