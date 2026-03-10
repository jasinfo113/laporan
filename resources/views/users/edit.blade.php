<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">Edit Akun: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h3 class="mb-4 border-b pb-2 text-lg font-bold dark:border-gray-700 dark:text-gray-100">Data Akun Primer</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ $user->name }}" class="shadow border rounded w-full py-2 px-3" required>
                            </div>
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Email (Untuk Login)</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="shadow border rounded w-full py-2 px-3" required>
                            </div>
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">Password Baru</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="shadow border rounded w-full py-2 px-3">
                            </div>
                            <div class="mb-4">
                                <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-200">NIK</label>
                                <input type="text" name="nik" value="{{ $user->nik }}" class="shadow border rounded w-full py-2 px-3">
                            </div>

                            <div class="mb-4 md:col-span-2">
                                <label class="mb-2 block text-sm font-bold text-purple-600 dark:text-purple-300">Hak Akses (Role)</label>
                                <select name="role" class="w-full rounded border bg-purple-50 px-3 py-2 shadow dark:bg-purple-900/30" required>
                                    <option value="pegawai" {{ $user->role === 'pegawai' ? 'selected' : '' }}>Pegawai Biasa</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-4 border-t pt-4 dark:border-gray-700">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                                Update Pengguna
                            </button>
                            <a href="{{ route('users.index') }}" class="font-bold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
