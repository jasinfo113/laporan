<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>eLTA - elektronik Laporan Tenaga Ahli | Disgulkarmat DKI</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

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
<body class="bg-gray-50 font-sans antialiased text-gray-900 dark:bg-gray-900 dark:text-gray-100">

    <nav class="fixed z-50 w-full border-b border-gray-100 bg-white/80 backdrop-blur-md transition-all dark:border-gray-700 dark:bg-gray-900/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <span class="hidden text-xl font-bold tracking-tight text-gray-800 dark:text-gray-100 sm:block">eLTA <span class="text-blue-600 dark:text-blue-400">elektronik Laporan Tenaga Ahli</span></span>
                </div>

                <div class="flex items-center gap-4">
                    <button type="button" data-theme-toggle class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                        <svg data-theme-toggle-icon-dark class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm4 8a4 4 0 1 1-8 0 4 4 0 0 1 8 0Zm-.464 4.95.707.707a1 1 0 0 1-1.414 1.414l-.707-.707a1 1 0 0 1 1.414-1.414Zm2.12-10.607a1 1 0 0 0-1.414 0l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0 0-1.414ZM17 11a1 1 0 1 1 0-2h1a1 1 0 1 1 0 2h-1Zm-7 6a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm-4.95-2.464a1 1 0 0 0 0 1.414l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707a1 1 0 0 0-1.414-1.414ZM4 10a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm.343-5.657a1 1 0 0 1 1.414 0l.707.707A1 1 0 0 1 5.05 6.464l-.707-.707a1 1 0 0 1 0-1.414Z"/>
                        </svg>
                        <svg data-theme-toggle-icon-light class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                            <path d="M17.293 13.293A8 8 0 0 1 6.707 2.707a8.001 8.001 0 1 0 10.586 10.586Z"/>
                        </svg>
                    </button>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-700 transition-colors hover:text-blue-600 dark:text-gray-200 dark:hover:text-blue-400">Ke Dashboard &rarr;</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 transition-colors hover:text-blue-600 dark:text-gray-200 dark:hover:text-blue-400">Log in</a>
                            @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
            <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
        </div>

        <div class="mx-auto mb-8 max-w-3xl px-4 sm:px-6 lg:px-8">
            <x-flash-alerts />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="mb-6 text-4xl font-extrabold tracking-tight text-gray-900 dark:text-gray-100 md:text-5xl lg:text-6xl">
                Sistem Pelaporan Kinerja <br class="hidden lg:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-500">Tenaga Ahli</span>
            </h1>
            <p class="mx-auto mb-10 mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-300 md:text-xl">
                Catat aktivitas harian, pantau produktivitas, dan cetak dokumen laporan bulanan secara otomatis dengan format Microsoft Word sesuai standar instansi.
            </p>

            <div class="flex justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg shadow-blue-500/30 transition-transform hover:-translate-y-1 flex items-center gap-2">
                        Buka Ruang Kerja Saya
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-full shadow-lg shadow-blue-500/30 transition-transform hover:-translate-y-1 flex items-center gap-2">
                        Mulai Sesi Login
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="border-t border-gray-100 bg-white py-20 dark:border-gray-700 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Kenapa Menggunakan eLTA?</h2>
                <p class="mt-4 text-gray-500 dark:text-gray-300">Didesain khusus untuk menyederhanakan administrasi laporan teknis.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 transition-shadow hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-gray-100">Input Harian Cepat</h3>
                    <p class="leading-relaxed text-gray-500 dark:text-gray-300">Pencatatan kegiatan harian berbasis scope pekerjaan. Tersedia fitur upload bukti dokumentasi kegiatan langsung dari sistem.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 transition-shadow hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-gray-100">Generate Word Otomatis</h3>
                    <p class="leading-relaxed text-gray-500 dark:text-gray-300">Tidak perlu lagi copy-paste ke MS Word. Sistem akan otomatis merekap kegiatan dalam format .docx siap cetak dan tandatangan.</p>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 transition-shadow hover:shadow-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-gray-100">Hak Akses Terpusat</h3>
                    <p class="leading-relaxed text-gray-500 dark:text-gray-300">Dilengkapi dengan Role-Based Access. Admin mengelola master data dan pegawai fokus pada laporan kinerja masing-masing.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="border-t border-gray-200 bg-white py-8 dark:border-gray-700 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-400">
            <p>&copy; {{ date('Y') }} Dinas Penanggulangan Kebakaran dan Penyelamatan Provinsi DKI Jakarta.</p>
            <p class="mt-1">Developed for Internal eLTA Team.</p>
        </div>
    </footer>

</body>
</html>

