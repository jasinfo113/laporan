<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Master Paket Pekerjaan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Daftar Paket Pekerjaan</h3>
                <a href="{{ route('job_packages.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Tambah Paket
                </a>
            </div>

            <x-data-table 
                :columns="$columns"
                :data="$packages"
                :server-side="true"
                empty-title="Belum ada paket pekerjaan"
                empty-description="Silahkan tambah paket pekerjaan baru terlebih dahulu."
                key-field="id"
            />
        </div>
    </div>
</x-app-layout>
