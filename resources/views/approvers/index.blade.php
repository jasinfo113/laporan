<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Master Pejabat / Penandatangan</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 flex justify-end">
            <a href="{{ route('approvers.create') }}" class="bg-blue-600 text-white font-bold py-2 px-4 rounded">+ Tambah Pejabat</a>
        </div>
        <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            <table class="w-full text-left border-collapse">
                <tr class="bg-gray-100 dark:bg-gray-700/50"><th class="border px-4 py-2 dark:border-gray-700">Nama</th><th class="border px-4 py-2 dark:border-gray-700">NIP</th><th class="border px-4 py-2 dark:border-gray-700">Jabatan</th><th class="border px-4 py-2 dark:border-gray-700">Aksi</th></tr>
                @foreach($approvers as $ap)
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->nama }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->nip }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->jabatan }}</td>
                    <td class="flex gap-2 border px-4 py-2 dark:border-gray-700">
                        <a href="{{ route('approvers.edit', $ap->id) }}" class="font-bold text-yellow-600 dark:text-yellow-400">Edit</a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div></div>
</x-app-layout>
