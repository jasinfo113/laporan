<x-app-layout>
    @php
        $canViewAllReports = Auth::user()->role === 'staff';
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ $canViewAllReports ? __('Daftar Laporan Semua Pegawai') : __('Daftar Laporan Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                    {{ $canViewAllReports ? 'Laporan Semua Pegawai' : 'Laporan Anda' }}
                </h3>
                @if (!$canViewAllReports)
                    <a href="{{ route('reports.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                        + Buat Laporan Baru
                    </a>
                @endif

            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                @if($canViewAllReports)
                                    <th class="border-b py-2 px-4 dark:border-gray-700">Pegawai</th>
                                @endif
                                <th class="border-b py-2 px-4 dark:border-gray-700">Bulan</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Tahun</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    @if($canViewAllReports)
                                        <td class="border-b py-2 px-4 dark:border-gray-700">{{ $report->user->name ?? '-' }}</td>
                                    @endif
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ date('F', mktime(0, 0, 0, $report->bulan, 10)) }}</td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ $report->tahun }}</td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">
                                        <x-icon-action
                                            as="a"
                                            :href="route('reports.show', $report->id)"
                                            color="blue"
                                            tooltip="Buka & Isi"
                                            tooltip-id="tt-report-open-{{ $report->id }}"
                                        >
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg> &nbsp;
                                            Buka & Isi
                                        </x-icon-action>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canViewAllReports ? '4' : '3' }}" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada laporan yang dibuat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $reports->links('components.flowbite-pagination') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
