<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ __('Daftar Laporan Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="relative mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Laporan Anda</h3>
                <a href="{{ route('reports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Buat Laporan Baru
                </a>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Bulan</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Tahun</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ date('F', mktime(0, 0, 0, $report->bulan, 10)) }}</td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ $report->tahun }}</td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">
                                        <a href="{{ route('reports.show', $report->id) }}" class="text-blue-600 hover:underline dark:text-blue-400">Buka & Isi Kegiatan</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada laporan yang dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
