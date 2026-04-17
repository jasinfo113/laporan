@php
    $status = $exception->getStatusCode();
    $message = config('app.debug') && $exception->getMessage()
        ? $exception->getMessage()
        : 'Terjadi kesalahan pada sistem. Silakan coba lagi beberapa saat lagi.';
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
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-12">
            <div class="absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-br from-blue-100 via-emerald-50 to-transparent blur-3xl dark:from-blue-900/30 dark:via-emerald-900/20"></div>

            <div class="w-full max-w-xl rounded-3xl border border-gray-200 bg-white p-8 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-red-500 dark:text-red-300">{{ $status }}</p>
                <h1 class="mt-4 text-3xl font-extrabold tracking-tight">Terjadi kesalahan pada sistem</h1>
                <p class="mt-4 text-base leading-relaxed text-gray-600 dark:text-gray-300">
                    {{ $message }}
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
