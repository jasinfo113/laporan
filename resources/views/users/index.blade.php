<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ __('Master Pegawai') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Daftar Tenaga Ahli / Pegawai</h3>
                <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
                    + Tambah Pegawai
                </a>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700/50">
                                <th class="border-b py-2 px-4 dark:border-gray-700">Nama</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">NIK</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Jabatan</th>
                                <th class="border-b py-2 px-4 dark:border-gray-700">Paket Pekerjaan</th>
                                <th class="border-b py-2 px-4 text-center dark:border-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="border-b py-2 px-4 font-semibold dark:border-gray-700">
                                        {{ $user->name }}<br>
                                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                                    </td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ $user->nik ?? '-' }}</td>
                                    <td class="border-b py-2 px-4 dark:border-gray-700">{{ $user->jabatan ?? '-' }}</td>

                                    <td class="border-b py-2 px-4 dark:border-gray-700">
                                        @if($user->jobPackage)
                                            <span class="font-medium text-blue-700 dark:text-blue-400">{{ $user->jobPackage->nama_paket }}</span><br>
                                            <span class="rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                {{ $user->jobPackage->scopes->count() }} Aktivitas
                                            </span>
                                        @else
                                            <span class="italic text-gray-400 dark:text-gray-500">Belum diset</span>
                                        @endif
                                    </td>

                                    <td class="border-b py-2 px-4 text-center dark:border-gray-700">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('users.edit', $user->id) }}" class="font-bold text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300">Edit</a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin mau hapus akun ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-bold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
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
