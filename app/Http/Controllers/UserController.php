<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Tampilkan user dengan pagination
        $users = User::with(['jobPackage.scopes'])
            ->orderBy('name')
            ->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create'); // Nggak perlu ngirim $packages lagi
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'nullable|string',
            'role' => 'required|in:admin,pegawai' // Tambah validasi role
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nik' => $request->nik,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Pegawai berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'nik' => 'nullable|string',
            'role' => 'required|in:admin,pegawai'
        ]);

        $data = $request->only(['name', 'email', 'nik', 'role']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pegawai berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pegawai berhasil dihapus!');
    }
}
