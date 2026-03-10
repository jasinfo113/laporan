<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
                Laporan Bulan: {{ date('F', mktime(0, 0, 0, $report->bulan, 10)) }} {{ $report->tahun }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.export', $report->id) }}" class="text-sm bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow">
                    🖨️ Cetak ke Word
                </a>
                <a href="{{ route('reports.index') }}" class="rounded bg-gray-500 px-4 py-2 text-sm text-white shadow hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ imageModalOpen: false, activeImage: '', activeCaption: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-bold dark:text-gray-100">Tambah Kegiatan Baru</h3>

                    <form action="{{ route('tasks.store', $report->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Tanggal</label>
                                <input type="date" name="tanggal" class="shadow border rounded w-full py-2 px-3 text-gray-700" min="{{ $report->tahun }}-{{ str_pad($report->bulan, 2, '0', STR_PAD_LEFT) }}-01" max="{{ $report->tahun }}-{{ str_pad($report->bulan, 2, '0', STR_PAD_LEFT) }}-{{ cal_days_in_month(CAL_GREGORIAN, $report->bulan, $report->tahun) }}" required>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Aktivitas / Ruang Lingkup</label>
                                <select name="scope_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih Aktivitas (Kosongkan jika Cuti/Libur) --</option>
                                    @foreach($scopes as $scope)
                                        <option value="{{ $scope->id }}">{{ $scope->kode_aktivitas }} - {{ $scope->uraian }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Deskripsi Pekerjaan</label>
                            <input type="text" name="deskripsi_pekerjaan" placeholder="Contoh: Meeting Awal Tahun..." class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                        </div>

                        <div class="mb-4">
                            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Upload Foto Bukti (Bisa pilih banyak file sekaligus)</label>
                            <input type="file" name="fotos[]" multiple accept="image/*" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Simpan Kegiatan
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <h3 class="mb-4 text-lg font-bold dark:text-gray-100">Daftar Kegiatan Bulan Ini</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700/50">
                                    <th class="border px-4 py-2 dark:border-gray-700">Tanggal</th>
                                    <th class="border px-4 py-2 dark:border-gray-700">Aktivitas</th>
                                    <th class="border px-4 py-2 dark:border-gray-700">Deskripsi</th>
                                    <th class="border px-4 py-2 dark:border-gray-700">Foto / Bukti</th>
                                    <th class="w-24 border px-4 py-2 text-center dark:border-gray-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyTasks as $task)
                                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ \Carbon\Carbon::parse($task->tanggal)->format('d M Y') }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ $task->scope ? $task->scope->kode_aktivitas : '-' }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ $task->deskripsi_pekerjaan }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">
                                            @if($task->taskImages->count() > 0)
                                                <div class="flex gap-2">
                                                    @foreach($task->taskImages as $image)
                                                        <button
                                                            type="button"
                                                            @click="
                                                                activeImage = '{{ asset('storage/' . $image->image_path) }}';
                                                                activeCaption = '{{ addslashes($task->deskripsi_pekerjaan) }}';
                                                                imageModalOpen = true;
                                                            "
                                                            class="focus:outline-none"
                                                        >
                                                            <img src="{{ asset('storage/' . $image->image_path) }}" class="h-12 w-12 object-cover rounded border hover:opacity-90 transition" alt="Bukti kegiatan">
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Tidak ada foto</span>
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 text-center dark:border-gray-700">
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Yakin mau hapus kegiatan ini beserta fotonya?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-icon-action
                                                    as="button"
                                                    type="submit"
                                                    color="red"
                                                    tooltip="Hapus"
                                                    tooltip-id="tt-task-delete-{{ $task->id }}"
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
                                        <td colspan="5" class="border px-4 py-4 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada kegiatan yang diinput.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{ $dailyTasks->links('components.flowbite-pagination') }}
                    </div>
                </div>
            </div>

            <div
                x-show="imageModalOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                @click.self="imageModalOpen = false"
                @keydown.escape.window="imageModalOpen = false"
            >
                <div class="relative w-full max-w-4xl">
                    <button
                        type="button"
                        class="absolute -top-10 right-0 rounded-md bg-white/10 px-3 py-1 text-sm text-white hover:bg-white/20"
                        @click="imageModalOpen = false"
                    >
                        Tutup
                    </button>

                    <img :src="activeImage" alt="Preview bukti kegiatan" class="max-h-[80vh] w-full rounded-lg object-contain">

                    <p class="mt-3 text-center text-sm text-gray-200" x-text="activeCaption"></p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
