<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Tambah Paket Pekerjaan</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div class="p-6">
                    <form action="{{ route('job_packages.store') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label class="mb-2 block text-lg font-bold text-gray-700 dark:text-gray-200">Nama Paket Pekerjaan</label>
                            <input type="text" name="nama_paket" placeholder="Contoh: Tenaga Ahli Programmer" class="shadow border rounded w-full py-3 px-4 text-lg" required>
                        </div>
                        <div class="mb-6 mt-4">
                            <label class="mb-2 block font-bold text-gray-700 dark:text-gray-200">Pejabat Penandatangan (PPTK)</label>
                            <select name="approver_id" class="shadow border rounded w-full py-3 px-4" required>
                                <option value="">-- Pilih Pejabat --</option>
                                @foreach($approvers as $approver)
                                    <option value="{{ $approver->id }}" {{ (isset($jobPackage) && $jobPackage->approver_id == $approver->id) ? 'selected' : '' }}>
                                        {{ $approver->nama }} ({{ $approver->jabatan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-6 border-t pt-6 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Daftar Aktivitas / Ruang Lingkup</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan atau sesuaikan rincian tugas untuk paket pekerjaan ini.</p>
                                </div>
                                <button type="button" onclick="addScopeRow()" class="bg-emerald-500 hover:bg-emerald-600 transition-colors text-white font-semibold py-2 px-4 rounded-md shadow-sm text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Baris
                                </button>
                            </div>

                            <div id="scopes-container" class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700/30">
                                <div class="scope-row flex flex-col items-start gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800 md:flex-row">

                                    <div class="w-full md:w-1/4">
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kode Aktivitas</label>
                                        <input type="text" name="scopes[0][kode_aktivitas]" value="Aktifitas 01" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2.5 px-3" required>
                                    </div>

                                    <div class="w-full md:flex-1">
                                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Uraian Ruang Lingkup</label>
                                        <textarea name="scopes[0][uraian]" placeholder="Deskripsikan pekerjaan..." class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2.5 px-3" rows="2" required></textarea>
                                    </div>

                                    <div class="w-full md:w-auto md:pt-6 flex justify-end">
                                        <button type="button" onclick="this.closest('.scope-row').remove()" class="bg-red-50 hover:bg-red-500 text-red-500 hover:text-white border border-red-200 hover:border-red-500 transition-all p-2.5 rounded-lg flex items-center justify-center" title="Hapus Aktivitas">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center gap-4 border-t pt-4 dark:border-gray-700">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 transition-colors text-white font-bold py-2.5 px-6 rounded-md shadow-sm">Simpan Data</button>
                            <a href="{{ route('job_packages.index') }}" class="py-2 font-semibold text-gray-500 transition-colors hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let scopeIndex = 1; // Mulai dari 1 karena 0 sudah ada di form awal
        function addScopeRow() {
            let html = `
                <div class="scope-row flex flex-col items-start gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800 md:flex-row">

                    <div class="w-full md:w-1/4">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kode Aktivitas</label>
                        <input type="text" name="scopes[${scopeIndex}][kode_aktivitas]" value="Aktifitas ${String(scopeIndex + 1).padStart(2, '0')}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2.5 px-3" required>
                    </div>

                    <div class="w-full md:flex-1">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Uraian Ruang Lingkup</label>
                        <textarea name="scopes[${scopeIndex}][uraian]" placeholder="Deskripsikan pekerjaan..." class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 py-2.5 px-3" rows="2" required></textarea>
                    </div>

                    <div class="w-full md:w-auto md:pt-6 flex justify-end">
                        <button type="button" onclick="this.closest('.scope-row').remove()" class="bg-red-50 hover:bg-red-500 text-red-500 hover:text-white border border-red-200 hover:border-red-500 transition-all p-2.5 rounded-lg flex items-center justify-center" title="Hapus Aktivitas">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>

                </div>
            `;
            document.getElementById('scopes-container').insertAdjacentHTML('beforeend', html);
            scopeIndex++;
        }
    </script>

</x-app-layout>
