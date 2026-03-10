<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Manajemen Kontrak Pegawai</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4 flex justify-end">
            <a href="{{ route('contracts.create') }}" class="bg-blue-600 text-white font-bold py-2 px-4 rounded">+ Buat Kontrak Baru</a>
        </div>
        <div class="overflow-x-auto rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            <table class="w-full text-left border-collapse">
                <tr class="bg-gray-100 dark:bg-gray-700/50"><th class="border px-4 py-2 dark:border-gray-700">Pegawai</th><th class="border px-4 py-2 dark:border-gray-700">Nama Kontrak</th><th class="border px-4 py-2 dark:border-gray-700">Masa Berlaku</th><th class="border px-4 py-2 dark:border-gray-700">Aksi</th></tr>
                @forelse($contracts as $c)
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="border px-4 py-2 font-bold dark:border-gray-700">{{ $c->user->name }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">
                        {{ $c->nama_kontrak }}<br>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $c->jobPackage->nama_paket }}</span>
                    </td>
                    <td class="border px-4 py-2 text-sm dark:border-gray-700">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d M Y') }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <x-icon-action
                                as="a"
                                :href="route('contracts.edit', $c->id)"
                                color="yellow"
                                tooltip="Edit"
                                tooltip-id="tt-contract-edit-{{ $c->id }}"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </x-icon-action>

                            <form action="{{ route('contracts.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Yakin hapus kontrak ini?');">
                                @csrf @method('DELETE')
                                <x-icon-action
                                    as="button"
                                    type="submit"
                                    color="red"
                                    tooltip="Hapus"
                                    tooltip-id="tt-contract-delete-{{ $c->id }}"
                                >
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5h6v2m-7 4v6m4-6v6m4-10v12a1 1 0 01-1 1H9a1 1 0 01-1-1V7h8z"/>
                                    </svg>
                                </x-icon-action>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="border px-4 py-4 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada data kontrak.</td>
                </tr>
                @endforelse
            </table>

            {{ $contracts->links('components.flowbite-pagination') }}
        </div>
    </div></div>
</x-app-layout>
