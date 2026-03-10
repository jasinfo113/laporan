<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Informasi Profil Pegawai') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            {{ __("Perbarui identitas diri dasar Anda di sini. Untuk perubahan detail kontrak pekerjaan, silakan hubungi Administrator.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-700/30">
            <h3 class="mb-4 border-b border-gray-200 pb-2 font-bold text-gray-700 dark:border-gray-700 dark:text-gray-200">Data Akun Primer</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" :value="__('Nama Lengkap (Sesuai KTP/Gelar)')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-white" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email (Untuk Login)')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-white" :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="nik" :value="__('NIK / NIP')" />
                    <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full bg-white" :value="old('nik', $user->nik)" />
                    <x-input-error class="mt-2" :messages="$errors->get('nik')" />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2 mt-4">
                <x-primary-button class="bg-blue-600 hover:bg-blue-700">{{ __('Simpan Identitas') }}</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="rounded-md bg-emerald-50 px-3 py-1 text-sm font-bold text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">
                        {{ __('✅ Berhasil Disimpan.') }}
                    </p>
                @endif
            </div>
        </div>
    </form>

    <div class="mt-8 rounded-xl border border-blue-100 bg-blue-50 p-5 shadow-sm dark:border-blue-800 dark:bg-blue-900/20">
        <h3 class="mb-4 flex items-center justify-between border-b border-blue-200 pb-2 font-bold text-blue-800 dark:border-blue-800 dark:text-blue-200">
            <span>Detail Pekerjaan & Kontrak (Aktif)</span>
            <span class="rounded-md bg-blue-200 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Read-Only</span>
        </h3>

        @php
            // Panggil Fungsi Sakti dari Model User untuk ngecek Kontrak Aktif
            $activeContract = Auth::user()->activeContract;
        @endphp

        @if($activeContract)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                <div>
                    <p class="font-bold text-blue-900 mb-1">Nama Kontrak / Termin</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">{{ $activeContract->nama_kontrak }}</p>
                </div>
                <div>
                    <p class="font-bold text-blue-900 mb-1">Jabatan Kontrak</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">{{ $activeContract->jabatan }}</p>
                </div>
                <div>
                    <p class="font-bold text-blue-900 mb-1">Paket Pekerjaan (Scope)</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">{{ $activeContract->jobPackage->nama_paket ?? '-' }}</p>
                </div>
                <div>
                    <p class="font-bold text-blue-900 mb-1">Masa Berlaku Kontrak</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 font-mono text-xs text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ \Carbon\Carbon::parse($activeContract->tanggal_mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($activeContract->tanggal_selesai)->format('d M Y') }}
                    </p>
                </div>
                <div>
                    <p class="font-bold text-blue-900 mb-1">No. SPK & Tanggal</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 font-mono text-xs text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ $activeContract->spk_nomor ?? '-' }}<br>
                        <span class="text-gray-500 dark:text-gray-400">{{ $activeContract->spk_tanggal ? \Carbon\Carbon::parse($activeContract->spk_tanggal)->format('d M Y') : '-' }}</span>
                    </p>
                </div>
                <div>
                    <p class="font-bold text-blue-900 mb-1">No. SPMK & Tanggal</p>
                    <p class="rounded border border-blue-100 bg-white px-3 py-2 font-mono text-xs text-gray-700 dark:border-blue-800 dark:bg-gray-800 dark:text-gray-200">
                        {{ $activeContract->spmk_nomor ?? '-' }}<br>
                        <span class="text-gray-500 dark:text-gray-400">{{ $activeContract->spmk_tanggal ? \Carbon\Carbon::parse($activeContract->spmk_tanggal)->format('d M Y') : '-' }}</span>
                    </p>
                </div>
            </div>
        @else
            <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                <svg class="mx-auto mb-3 h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <p>Belum ada kontrak kerja yang aktif saat ini.</p>
                <p class="text-xs mt-1">Silakan hubungi Administrator untuk pembaruan kontrak.</p>
            </div>
        @endif
    </div>
</section>
