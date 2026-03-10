<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Form Data Pejabat</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
            <form action="{{ isset($approver) ? route('approvers.update', $approver->id) : route('approvers.store') }}" method="POST">
                @csrf
                @if(isset($approver)) @method('PUT') @endif

                <div class="mb-4">
                    <label class="mb-2 block font-bold text-gray-700 dark:text-gray-200">Nama Pejabat (beserta gelar)</label>
                    <input type="text" name="nama" value="{{ $approver->nama ?? '' }}" class="border rounded w-full py-2 px-3" required>
                </div>
                <div class="mb-4">
                    <label class="mb-2 block font-bold text-gray-700 dark:text-gray-200">NIP</label>
                    <input type="text" name="nip" value="{{ $approver->nip ?? '' }}" class="border rounded w-full py-2 px-3" required>
                </div>
                <div class="mb-4">
                    <label class="mb-2 block font-bold text-gray-700 dark:text-gray-200">Jabatan Penandatangan (cth: Pejabat Pelaksana Teknis Kegiatan)</label>
                    <input type="text" name="jabatan" value="{{ $approver->jabatan ?? '' }}" class="border rounded w-full py-2 px-3" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded">Simpan</button>
            </form>
        </div>
    </div></div>
</x-app-layout>
