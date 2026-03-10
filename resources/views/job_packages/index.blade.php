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

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700/50">
                                <th class="border-b py-2 px-4 dark:border-gray-700">Nama Paket Pekerjaan</th>
                                <th class="border-b py-2 px-4 text-center dark:border-gray-700">Jumlah Aktivitas (Scope)</th>
                                <th class="border-b py-2 px-4 text-center dark:border-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packages as $package)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="border-b py-2 px-4 font-semibold dark:border-gray-700">{{ $package->nama_paket }}</td>
                                    <td class="border-b py-2 px-4 text-center dark:border-gray-700">
                                        <span class="rounded bg-blue-100 px-2.5 py-0.5 font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ $package->scopes_count }}</span>
                                    </td>
                                    <td class="border-b py-2 px-4 text-center dark:border-gray-700">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('job_packages.edit', $package->id) }}" class="font-bold text-yellow-600 hover:underline dark:text-yellow-400">Detail/Edit</a>
                                            <form action="{{ route('job_packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Yakin hapus paket ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="font-bold text-red-600 hover:underline dark:text-red-400">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
