<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />

<title>
    {{ filled($title ?? null) ? 'Teja Perceka - ' . $title : 'Teja Perceka' }}
</title>

<link rel="icon" type="image/png" href="{{ asset('assets/images/logo-bumdes-teja-perceka.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet">

@fonts

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('swal-alert', (events) => {
            const event = Array.isArray(events) ? events[0] : events;
            window.Swal.fire({
                icon: event.icon ?? 'info',
                title: event.title ?? '',
                text: event.text ?? '',
                timer: event.timer ?? (event.icon === 'success' ? 2500 : undefined),
                timerProgressBar: event.icon === 'success',
                showConfirmButton: event.icon !== 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#f59e0b',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl font-sans',
                    title: 'text-zinc-900 font-semibold',
                    htmlContainer: 'text-zinc-600',
                    timerProgressBar: 'bg-amber-400',
                },
            });
        });
    });
</script>

@if(session('swal'))
    <script>
        const swalData = @json(session('swal'));
        (function waitForSwal(attempts) {
            if (window.Swal) {
                window.Swal.fire({
                    icon: swalData.icon ?? 'info',
                    title: swalData.title ?? '',
                    text: swalData.text ?? '',
                    timer: swalData.icon === 'success' ? 2500 : undefined,
                    timerProgressBar: swalData.icon === 'success',
                    showConfirmButton: swalData.icon !== 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f59e0b',
                    customClass: {
                        popup: 'rounded-2xl shadow-2xl font-sans',
                        title: 'text-zinc-900 font-semibold',
                        htmlContainer: 'text-zinc-600',
                        timerProgressBar: 'bg-amber-400',
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
