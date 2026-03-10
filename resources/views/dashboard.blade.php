<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 overflow-hidden border-l-4 border-blue-500 bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Halo, {{ Auth::user()->name }}! 👋</h3>
                        <p class="mt-1 text-gray-500 dark:text-gray-300">Selamat datang di Sistem Pelaporan Kinerja. Jangan lupa catat kegiatan harianmu ya!</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="flex bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm items-center gap-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Laporan Baru
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-transform hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Kegiatan Bulan Ini</p>
                        <h4 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">{{ $kegiatanBulanIni }} <span class="text-sm font-normal text-gray-400 dark:text-gray-500">tugas</span></h4>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-transform hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-4 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Total Laporan Dicetak</p>
                        <h4 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">{{ $totalLaporan }} <span class="text-sm font-normal text-gray-400 dark:text-gray-500">dokumen</span></h4>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-transform hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-4 bg-amber-50 text-amber-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium uppercase text-gray-500 dark:text-gray-400">Target Aktivitas (Scope)</p>
                        <h4 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">{{ $targetAktivitas }} <span class="text-sm font-normal text-gray-400 dark:text-gray-500">item</span></h4>
                    </div>
                </div>

                <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-transform hover:-translate-y-1 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-4 bg-rose-50 text-rose-600 rounded-lg">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Sisa Cuti {{ $tahunSekarang }}</p>
                        <h4 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">
                            {{ $sisaCuti }} <span class="text-sm font-normal text-gray-400 dark:text-gray-500">/ {{ $totalJatahCuti }} hari</span>
                        </h4>
                    </div>
                </div>

            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2 rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 border-b pb-2 text-lg font-bold text-gray-800 dark:border-gray-700 dark:text-gray-100">Grafik Produktivitas Bulan Ini</h3>
                    <div class="relative w-full h-72">
                        <canvas id="productivityChart"></canvas>
                    </div>
                </div>

                <div class="flex h-full flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 border-b pb-2 text-lg font-bold text-gray-800 dark:border-gray-700 dark:text-gray-100">Aktivitas Terakhir</h3>

                    <div class="space-y-4 flex-grow">
                        @forelse($recentTasks as $task)
                            <div class="flex items-start gap-3 border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                                <div class="flex-shrink-0 bg-blue-50 text-blue-600 rounded-lg p-2 text-center w-12">
                                    <span class="block text-sm font-extrabold">{{ \Carbon\Carbon::parse($task->tanggal)->format('d') }}</span>
                                    <span class="block text-[10px] uppercase font-semibold">{{ \Carbon\Carbon::parse($task->tanggal)->format('M') }}</span>
                                </div>
                                <div class="flex-grow overflow-hidden">
                                    <h4 class="truncate text-sm font-bold text-gray-800 dark:text-gray-100" title="{{ $task->scope ? $task->scope->kode_aktivitas : 'Lainnya' }}">
                                        {{ $task->scope ? $task->scope->kode_aktivitas : 'Aktivitas Tambahan' }}
                                    </h4>
                                    <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $task->deskripsi_pekerjaan }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <svg class="mx-auto h-10 w-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-sm">Belum ada aktivitas yang dicatat.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4 pt-3 border-t text-center">
                        <a href="{{ route('reports.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-bold transition-colors">Lihat Semua Laporan &rarr;</a>
                    </div>
                </div>
            </div>
            </div> </div> <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('productivityChart').getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');
            const axisColor = isDark ? '#9ca3af' : '#6b7280';
            const gridColor = isDark ? '#374151' : '#f3f4f6';
            const tooltipBg = isDark ? 'rgba(17, 24, 39, 0.95)' : 'rgba(17, 24, 39, 0.8)';
            const pointBg = isDark ? '#1f2937' : '#ffffff';

            // Ambil data yang dilempar dari Controller via JSON
            const labels = {!! json_encode($chartLabels) !!};
            const data = {!! json_encode($chartData) !!};

            new Chart(ctx, {
                type: 'line', // Tipe grafik garis
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Pekerjaan',
                        data: data,
                        borderColor: '#2563eb', // Warna biru Tailwind
                        backgroundColor: 'rgba(37, 99, 235, 0.1)', // Warna biru transparan buat efek fill
                        borderWidth: 2,
                        pointBackgroundColor: pointBg,
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.3 // Bikin garisnya agak melengkung estetik (nggak kaku patah-patah)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }, // Sembunyikan legend karena cuma 1 garis
                        tooltip: {
                            backgroundColor: tooltipBg,
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false, // Hilangkan kotak warna di tooltip
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1, // Pastikan angkanya bilangan bulat (nggak ada 1.5 kegiatan)
                                color: axisColor
                            },
                            grid: { color: gridColor, drawBorder: false }
                        },
                        x: {
                            ticks: { color: axisColor },
                            grid: { display: false, drawBorder: false }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
