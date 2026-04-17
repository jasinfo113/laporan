@php
    $status = $exception->getStatusCode();
    $message = match ($status) {
        403 => 'Akses ke halaman ini ditolak.',
        404 => 'Halaman yang Anda cari tidak ditemukan.',
        419 => 'Sesi Anda sudah berakhir. Silakan coba lagi.',
        default => $exception->getMessage() ?: 'Terjadi kesalahan pada permintaan Anda.',
    };
    $backUrl = filled(request()->headers->get('referer')) ? url()->previous() : url('/');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $status }} | {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-xl rounded-3xl border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-blue-600 dark:text-blue-400">{{ $status }}</p>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Terjadi kendala pada halaman ini</h1>
                <p class="mt-4 text-base leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ config('app.debug') && $exception->getMessage() ? $exception->getMessage() : $message }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $backUrl }}" class="rounded-full bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-blue-700">
                        Kembali
                    </a>
                    <a href="{{ url('/') }}" class="rounded-full border border-gray-300 px-5 py-2.5 text-sm font-bold text-gray-700 transition-colors hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Beranda
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
