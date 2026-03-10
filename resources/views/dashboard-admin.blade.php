<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ __('Administrator Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 overflow-hidden border-l-4 border-indigo-500 bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-indigo-700">Selamat datang kembali, {{ Auth::user()->name }}! 👋</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Berikut adalah ringkasan data kepegawaian Tenaga Ahli (ELTA) saat ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

                <div class="flex items-center rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Tenaga Ahli</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalPegawai }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Orang</span></p>
                    </div>
                </div>

                <div class="flex items-center rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Kontrak Aktif</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $kontrakAktif }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Dokumen</span></p>
                    </div>
                </div>

                <div class="flex items-center rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Laporan Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $laporanBulanIni }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Draft/Final</span></p>
                    </div>
                </div>

                <div class="flex items-center rounded-lg border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-3 rounded-full bg-rose-100 text-rose-600 mr-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Sedang Cuti / Libur</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $cutiHariIni }} <span class="text-sm font-normal text-gray-500 dark:text-gray-400">Orang Hari Ini</span></p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 p-6 text-gray-900 dark:border-gray-700 dark:bg-gray-700/40">
                    <h3 class="font-bold text-gray-700 dark:text-gray-100">Jadwal Cuti Mendatang / Terkini</h3>
                    <a href="{{ route('leaves.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Lihat Semua Data Cuti &rarr;</a>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                <th class="px-6 py-3 text-sm font-bold text-gray-600 dark:text-gray-300">Nama Pegawai</th>
                                <th class="px-6 py-3 text-sm font-bold text-gray-600 dark:text-gray-300">Tanggal Cuti</th>
                                <th class="px-6 py-3 text-sm font-bold text-gray-600 dark:text-gray-300">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pegawaiCuti as $cuti)
                            <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-100">{{ $cuti->user->name }}</td>
                                <td class="px-6 py-4 text-sm font-mono text-gray-600 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($cuti->tanggal_cuti)->locale('id')->isoFormat('D MMMM Y') }}
                                    @if(\Carbon\Carbon::parse($cuti->tanggal_cuti)->isToday())
                                        <span class="ml-2 bg-rose-100 text-rose-700 text-xs font-bold px-2 py-1 rounded">Hari Ini</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $cuti->keterangan }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">Belum ada pengajuan cuti dalam waktu dekat.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
