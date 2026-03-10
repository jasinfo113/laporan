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
                @forelse($approvers as $ap)
                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->nama }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->nip }}</td>
                    <td class="border px-4 py-2 dark:border-gray-700">{{ $ap->jabatan }}</td>
                    <td class="flex gap-2 border px-4 py-2 dark:border-gray-700">
                        <x-icon-action
                            as="a"
                            :href="route('approvers.edit', $ap->id)"
                            color="yellow"
                            tooltip="Edit"
                            tooltip-id="tt-ap-edit-{{ $ap->id }}"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                            </svg>
                        </x-icon-action>
                        <form action="{{ route('approvers.destroy', $ap->id) }}" method="POST" onsubmit="return confirm('Yakin hapus pejabat ini?');">
                            @csrf
                            @method('DELETE')
                            <x-icon-action
                                as="button"
                                type="submit"
                                color="red"
                                tooltip="Hapus"
                                tooltip-id="tt-ap-delete-{{ $ap->id }}"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5h6v2m-7 4v6m4-6v6m4-10v12a1 1 0 01-1 1H9a1 1 0 01-1-1V7h8z"/>
                                </svg>
                            </x-icon-action>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="border px-4 py-4 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada data pejabat.</td>
                </tr>
                @endforelse
            </table>

            {{ $approvers->links('components.flowbite-pagination') }}
        </div>
    </div></div>
</x-app-layout>
