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

            <x-data-table 
                :columns="$columns"
                :data="$reports"
                :server-side="true"
                empty-title="Belum ada laporan yang dibuat"
                empty-description="Silahkan buat laporan bulanan baru terlebih dahulu."
                key-field="id"
            />
        </div>
    </div>
</x-app-layout>
