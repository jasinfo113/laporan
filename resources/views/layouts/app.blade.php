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
    <body class="bg-gray-50 font-sans antialiased dark:bg-gray-900">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div class="mx-auto max-w-screen-xl px-4 py-6">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="mx-auto max-w-screen-xl px-4 py-6">
                {{ $slot }}
            </main>
        </div>
        <style>
            .select2-dropdown {
                overflow: hidden !important;
                border-radius: 6px !important;
            }

            .select2-results__options {
                max-height: 190px !important; /* Kunci maksimal tingginya di sini */
                overflow-y: auto !important;
                overflow-x: hidden !important;
            }

            .select2-container--default .select2-results__option {
                white-space: normal !important;
                word-wrap: break-word !important;
                padding: 10px 12px !important; /* Bantalan atas-bawah biar nggak sumpek */
                line-height: 1.4 !important;
            }

            .select2-results__option:last-child {
                padding-bottom: 16px !important;
            }
        </style>
    </body>
</html>
