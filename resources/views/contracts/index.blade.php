<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Manajemen Kontrak Pegawai</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 flex justify-end">
            <a href="{{ route('contracts.create') }}" class="bg-blue-600 text-white font-bold py-2 px-4 rounded">+ Buat Kontrak Baru</a>
        </div>
        <x-data-table 
            :columns="$columns"
            :data="$contracts"
            :server-side="true"
            empty-title="Belum ada data kontrak"
            empty-description="Silahkan tambah kontrak baru terlebih dahulu."
            key-field="id"
        />
    </div></div>
</x-app-layout>
