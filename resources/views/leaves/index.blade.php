<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Riwayat Cuti Tahunan</h2>
    </x-slot>

    <div class="py-12"><div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="relative mb-4 rounded border border-emerald-400 bg-emerald-100 px-4 py-3 text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @elseif (session('error'))
            <div class="relative mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

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

        @if(Auth::user()->role !== 'admin')
        <div class="mb-4 flex justify-end">
            <a href="{{ route('leaves.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Ajukan Cuti Baru
            </a>
        </div>
        @endif

        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="p-6 text-gray-900 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 dark:border-gray-700 dark:bg-gray-700/40">
                            @if(Auth::user()->role === 'admin')
                                <th class="px-4 py-3 font-bold text-gray-600 dark:text-gray-300">Nama Pegawai</th>
                            @endif
                            <th class="px-4 py-3 font-bold text-gray-600 dark:text-gray-300">Tanggal Cuti</th>
                            <th class="px-4 py-3 font-bold text-gray-600 dark:text-gray-300">Keterangan</th>
                            <th class="w-24 px-4 py-3 font-bold text-gray-600 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leaves as $leave)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            @if(Auth::user()->role === 'admin')
                                <td class="px-4 py-3 text-gray-800 font-semibold">{{ $leave->user->name ?? 'Tidak Diketahui' }}</td>
                            @endif

                            <td class="px-4 py-3 font-semibold text-gray-800">{{ \Carbon\Carbon::parse($leave->tanggal_cuti)->locale('id')->isoFormat('dddd, D MMMM Y') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $leave->keterangan }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan/menghapus cuti ini?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 hover:bg-red-100 py-1.5 px-3 rounded transition-colors">Batal</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ Auth::user()->role === 'admin' ? '4' : '3' }}" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat pengajuan cuti.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div></div>
</x-app-layout>
