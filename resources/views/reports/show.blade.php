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

    <div class="py-12">
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
                                @forelse($report->dailyTasks->sortBy('tanggal') as $task)
                                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ \Carbon\Carbon::parse($task->tanggal)->format('d M Y') }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ $task->scope ? $task->scope->kode_aktivitas : '-' }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">{{ $task->deskripsi_pekerjaan }}</td>
                                        <td class="border px-4 py-2 dark:border-gray-700">
                                            @if($task->taskImages->count() > 0)
                                                <div class="flex gap-2">
                                                    @foreach($task->taskImages as $image)
                                                        <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $image->image_path) }}" class="h-12 w-12 object-cover rounded border">
                                                        </a>
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
                                                <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 hover:bg-red-100 py-1.5 px-3 rounded transition-colors">
                                                    Hapus
                                                </button>
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
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
