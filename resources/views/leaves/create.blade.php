<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Form Pengajuan Cuti</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 text-blue-600 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-blue-900 dark:text-blue-200">Informasi Sisa Cuti Tahun {{ $tahunSekarang }}</p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">Pastikan sisa cuti kamu mencukupi sebelum mengajukan.</p>
                </div>
            </div>
            <div class="text-center">
                <span class="block text-2xl font-extrabold text-blue-700 dark:text-blue-300">{{ $sisaCuti }}</span>
                <span class="block text-[10px] font-bold uppercase text-blue-500 dark:text-blue-400">HARI</span>
            </div>
        </div>

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="p-6 text-gray-900">

                @if (session('error'))
                    <div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-600 dark:bg-red-900/30 dark:text-red-300">{{ session('error') }}</div>
                @endif

                <form action="{{ route('leaves.store') }}" method="POST">
                    @csrf

                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Pilih Tanggal Cuti</label>
                        <input type="date" name="tanggal_cuti" class="shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 rounded-md w-full py-2.5 px-3" required>
                    </div>

                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Keterangan / Alasan</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Sakit, Acara Keluarga, dll." class="shadow-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 rounded-md w-full py-2.5 px-3" required>
                    </div>

                    <div class="mt-8 flex items-center gap-4 border-t pt-5 dark:border-gray-700">
                        @if($sisaCuti > 0)
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors">
                                Ajukan Sekarang
                            </button>
                        @else
                            <button type="button" disabled class="bg-gray-300 text-gray-500 font-bold py-2.5 px-6 rounded-lg cursor-not-allowed">
                                Jatah Cuti Habis
                            </button>
                        @endif
                        <a href="{{ route('leaves.index') }}" class="font-bold text-gray-500 transition-colors hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div></div>
</x-app-layout>
