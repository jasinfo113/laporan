<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (() => {
                try {
                    const theme = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    if (theme === 'dark' || (!theme && prefersDark)) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>
    </head>
    <body class="bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-8">
            <div class="absolute right-6 top-6">
                <button type="button" data-theme-toggle class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                    <svg data-theme-toggle-icon-dark class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm4 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-.464 4.95.707.707a1 1 0 0 1-1.414 1.414l-.707-.707a1 1 0 0 1 1.414-1.414Zm2.12-10.607a1 1 0 0 0-1.414 0l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0 0-1.414ZM17 11a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2h-1Zm-7 6a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm-4.95-2.464a1 1 0 0 0 0 1.414l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0-1.414-1.414ZM4 10a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm.343-5.657a1 1 0 0 1 1.414 0l.707.707A1 1 0 0 1 5.05 6.464l-.707-.707a1 1 0 0 1 0-1.414Z"/>
                    </svg>
                    <svg data-theme-toggle-icon-light class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                        <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8.001 8.001 0 1 0 10.586 10.586Z"/>
                    </svg>
                </button>
            </div>

            <div class="mb-6">
                <a href="/">
                    <x-application-logo class="h-16 w-16 fill-current text-gray-700 dark:text-gray-200" />
                </a>
            </div>

            <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <x-flash-alerts />
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
