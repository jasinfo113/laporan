<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">Edit Kontrak Pegawai</h2></x-slot>

    <div class="py-12"><div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            <form action="{{ route('contracts.update', $contract->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Pilih Pegawai</label>
                        <select name="user_id" class="w-full rounded border bg-gray-50 px-3 py-2 dark:bg-gray-700" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $contract->user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Paket Pekerjaan (Scope)</label>
                        <select name="job_package_id" class="w-full rounded border bg-gray-50 px-3 py-2 dark:bg-gray-700" required>
                            @foreach($packages as $p)
                                <option value="{{ $p->id }}" {{ $contract->job_package_id == $p->id ? 'selected' : '' }}>{{ $p->nama_paket }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Nama Kontrak (Cth: Tahap 1 2026)</label>
                        <input type="text" name="nama_kontrak" value="{{ $contract->nama_kontrak }}" class="border rounded w-full py-2 px-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Jabatan Kontrak</label>
                        <input type="text" name="jabatan" value="{{ $contract->jabatan }}" class="border rounded w-full py-2 px-3" required>
                    </div>
                    <div class="mb-4 md:col-span-2">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Tujuan Pekerjaan</label>
                        <textarea name="tujuan" rows="3" class="border rounded w-full py-2 px-3" placeholder="Contoh: Mengembangkan sistem informasi internal...">{{ $contract->tujuan }}</textarea>
                    </div>
                    <div class="mb-4 md:col-span-2">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Sasaran Pekerjaan</label>
                        <textarea name="sasaran" rows="3" class="border rounded w-full py-2 px-3" placeholder="Contoh: Terciptanya aplikasi pelaporan yang efisien...">{{ $contract->sasaran }}</textarea>
                    </div>
                    <div class="mb-4 md:col-span-2">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Ruang Lingkup Pekerjaan</label>
                        <textarea name="ruang_lingkup" rows="3" class="border rounded w-full py-2 px-3" placeholder="Contoh: Pengembangan modul pelaporan, integrasi dengan sistem lain...">{{ $contract->ruang_lingkup }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ $contract->tanggal_mulai }}" class="border rounded w-full py-2 px-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ $contract->tanggal_selesai }}" class="border rounded w-full py-2 px-3" required>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1 block font-bold text-gray-700 dark:text-gray-200">Jatah Cuti (Hari)</label>
                        <input type="number" name="kuota_cuti" value="{{ $contract->kuota_cuti }}" class="border rounded w-full py-2 px-3" required>
                    </div>
                </div>

                <h3 class="mb-2 mt-4 border-b pb-1 font-bold text-gray-800 dark:border-gray-700 dark:text-gray-100">Data SPK & SPMK</h3>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="spk_nomor" value="{{ $contract->spk_nomor }}" placeholder="No. SPK" class="border rounded w-full py-2 px-3">
                    <input type="date" name="spk_tanggal" value="{{ $contract->spk_tanggal }}" class="border rounded w-full py-2 px-3">
                    <input type="text" name="spmk_nomor" value="{{ $contract->spmk_nomor }}" placeholder="No. SPMK" class="border rounded w-full py-2 px-3">
                    <input type="date" name="spmk_tanggal" value="{{ $contract->spmk_tanggal }}" class="border rounded w-full py-2 px-3">
                </div>

                <div class="flex items-center gap-4 mt-6 border-t pt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Update Kontrak</button>
                    <a href="{{ route('contracts.index') }}" class="font-bold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Batal</a>
                </div>
            </form>
        </div>
    </div></div>
</x-app-layout>
