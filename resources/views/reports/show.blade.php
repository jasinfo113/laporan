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

    <div
        class="py-12"
        x-data="{
            imageModalOpen: false,
            activeImage: '',
            activeCaption: '',
            editModalOpen: false,
            editAction: '',
            editForm: {
                tanggal: '',
                scope_id: '',
                deskripsi_pekerjaan: '',
            },
            openEditTask(task) {
                this.editAction = task.action;
                this.editForm.tanggal = task.tanggal;
                this.editForm.scope_id = task.scope_id;
                this.editForm.deskripsi_pekerjaan = task.deskripsi_pekerjaan;
                this.editModalOpen = true;
            },
        }"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700 dark:border-green-700 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="relative mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Upload Foto Bukti (Bisa pilih banyak file sekaligus, atau <span class="text-blue-500 font-bold">Ctrl+V untuk Paste Screenshot</span>)
                            </label>
                            <input type="file" id="foto_bukti" name="task_images[]" multiple accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                        </div>

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Simpan Kegiatan
                        </button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <div class="relative mb-4 flex flex-col items-center justify-center sm:flex-row sm:min-h-[44px]">

                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 text-center">
                            Daftar Kegiatan Bulan Ini
                        </h3>

                        <div class="mt-3 flex w-full justify-center sm:absolute sm:right-0 sm:top-1/2 sm:mt-0 sm:-translate-y-1/2 sm:w-auto">
                            <form action="{{ route('reports.show', $report->id) }}" method="GET" class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Tampilkan:</label>

                                <select data-native-select name="limit" onchange="this.form.submit()" class="w-[130px] bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 cursor-pointer">
                                    <option value="10" {{ request('limit') == '10' ? 'selected' : '' }}>10 Baris</option>
                                    <option value="25" {{ request('limit') == '25' ? 'selected' : '' }}>25 Baris</option>
                                    <option value="50" {{ request('limit') == '50' ? 'selected' : '' }}>50 Baris</option>
                                    <option value="all" {{ request('limit') == 'all' ? 'selected' : '' }}>Semua Data</option>
                                </select>
                            </form>
                        </div>

                    </div>
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
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($task->taskImages as $image)
                                                        <div class="w-20 rounded-xl">
                                                            <button
                                                                type="button"
                                                                @click="
                                                                    activeImage = '{{ asset('storage/' . $image->image_path) }}';
                                                                    activeCaption = '{{ addslashes($task->deskripsi_pekerjaan) }}';
                                                                    imageModalOpen = true;
                                                                "
                                                                class="block focus:outline-none"
                                                                tooltip="Lihat Foto"
                                                                tooltip-id="tt-image-{{ $image->id }}"
                                                            >
                                                                <img src="{{ asset('storage/' . $image->image_path) }}" class="h-20 w-20 object-cover rounded-t hover:opacity-90 transition" alt="Bukti kegiatan" tooltip-id="tt-image-view-{{ $image->id }}">
                                                            </button>
                                                            <form action="{{ route('task-images.destroy', $image->id) }}" method="POST" onsubmit="return confirm('Hapus foto bukti ini saja?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button
                                                                    type="submit"
                                                                    tooltip="Hapus Foto"
                                                                    tooltip-id="tt-image-delete-{{ $image->id }}"
                                                                    class="block w-full bg-red-600 px-2 py-1 text-center text-xs font-bold leading-none rounded-b text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400"
                                                                >
                                                                    hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">Tidak ada foto</span>
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 text-center dark:border-gray-700">
                                            <div class="flex items-center justify-center gap-2">
                                                <x-icon-action
                                                    as="button"
                                                    type="button"
                                                    color="yellow"
                                                    tooltip="Edit"
                                                    tooltip-id="tt-task-edit-{{ $task->id }}"
                                                    data-action="{{ route('tasks.update', $task->id) }}"
                                                    data-tanggal="{{ \Carbon\Carbon::parse($task->tanggal)->format('Y-m-d') }}"
                                                    data-scope-id="{{ $task->scope_id ?? '' }}"
                                                    data-deskripsi="{{ $task->deskripsi_pekerjaan }}"
                                                    x-on:click="openEditTask({
                                                        action: $el.dataset.action,
                                                        tanggal: $el.dataset.tanggal,
                                                        scope_id: $el.dataset.scopeId || '',
                                                        deskripsi_pekerjaan: $el.dataset.deskripsi
                                                    })"
                                                >
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                                    </svg>
                                                </x-icon-action>

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
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="border px-4 py-4 text-center text-gray-500 dark:border-gray-700 dark:text-gray-400">Belum ada kegiatan yang diinput.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if($dailyTasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            {{ $dailyTasks->links('components.flowbite-pagination') }}
                        @endif
                    </div>
                </div>
            </div>

            <div
                x-show="editModalOpen"
                x-transition.opacity
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                @click.self="editModalOpen = false"
                @keydown.escape.window="editModalOpen = false"
            >
                <div class="w-full max-w-2xl overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Edit Kegiatan</h3>
                        <button type="button" class="rounded-md px-2 py-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700" @click="editModalOpen = false">
                            Tutup
                        </button>
                    </div>

                    <form :action="editAction" method="POST" enctype="multipart/form-data" class="p-6">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="edit_tanggal" class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Tanggal</label>
                                <input
                                    type="date"
                                    id="edit_tanggal"
                                    name="tanggal"
                                    x-model="editForm.tanggal"
                                    class="shadow border rounded w-full py-2 px-3 text-gray-700"
                                    min="{{ $report->tahun }}-{{ str_pad($report->bulan, 2, '0', STR_PAD_LEFT) }}-01"
                                    max="{{ $report->tahun }}-{{ str_pad($report->bulan, 2, '0', STR_PAD_LEFT) }}-{{ cal_days_in_month(CAL_GREGORIAN, $report->bulan, $report->tahun) }}"
                                    required
                                >
                            </div>

                            <div>
                                <label for="edit_scope_id" class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Aktivitas / Ruang Lingkup</label>
                                <select data-native-select id="edit_scope_id" name="scope_id" x-model="editForm.scope_id" class="shadow border rounded w-full py-2 px-3 text-gray-700">
                                    <option value="">-- Pilih Aktivitas (Kosongkan jika Cuti/Libur) --</option>
                                    @foreach($scopes as $scope)
                                        <option value="{{ $scope->id }}">{{ $scope->kode_aktivitas }} - {{ $scope->uraian }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="edit_deskripsi_pekerjaan" class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Deskripsi Pekerjaan</label>
                            <input
                                type="text"
                                id="edit_deskripsi_pekerjaan"
                                name="deskripsi_pekerjaan"
                                x-model="editForm.deskripsi_pekerjaan"
                                class="shadow border rounded w-full py-2 px-3 text-gray-700"
                                required
                            >
                        </div>

                        <div class="mb-6">
                            <label for="edit_foto_bukti" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Tambah Foto Bukti Baru (opsional)
                            </label>
                            <input type="file" id="edit_foto_bukti" name="task_images[]" multiple accept="image/*" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                        </div>

                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="rounded bg-gray-500 px-4 py-2 text-sm font-bold text-white shadow hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500" @click="editModalOpen = false">
                                Batal
                            </button>
                            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Kita pantau setiap kali user nekan Ctrl+V / Paste di mana aja di halaman ini
            document.addEventListener('paste', function (e) {
                const fileInput = document.getElementById('foto_bukti');
                if (!fileInput) return;

                const items = (e.clipboardData || window.clipboardData).items;
                let hasImage = false;

                // Bikin wadah DataTransfer baru (karena input.files bawaan itu Read-Only)
                const dataTransfer = new DataTransfer();

                // 1. Ambil dulu file-file yang udah ada di inputan (biar gak ketimpa)
                for (let i = 0; i < fileInput.files.length; i++) {
                    dataTransfer.items.add(fileInput.files[i]);
                }

                // 2. Cek isi clipboard, ada gambar nggak?
                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const blob = items[i].getAsFile();

                        // Bikin nama file otomatis biar rapi pas masuk database
                        const date = new Date();
                        const fileName = 'Screenshot_' + date.getTime() + '.png';

                        // Bungkus jadi File object dan masukin ke wadah
                        const file = new File([blob], fileName, { type: blob.type });
                        dataTransfer.items.add(file);
                        hasImage = true;
                    }
                }

                // 3. Kalau beneran ada gambar yang di-paste, masukin ke input file
                if (hasImage) {
                    fileInput.files = dataTransfer.files;

                    // Opsional: Kasih notif biar user tau paste-nya sukses
                    alert('Screenshot berhasil ditambahkan ke form!');
                }
            });
        });
    </script>
</x-app-layout>
