<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\User;
use App\Models\JobPackage;

class ContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contracts = Contract::with(['user', 'jobPackage'])
            ->orderBy('tanggal_mulai', 'desc')
            ->paginate(10);
        return view('contracts.index', compact('contracts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'pegawai')->get();
        $packages = JobPackage::all();
        return view('contracts.create', compact('users', 'packages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'job_package_id' => 'required|exists:job_packages,id',
            'nama_kontrak' => 'required|string',
            'jabatan' => 'required|string',
            'tujuan' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kuota_cuti' => 'required|integer|min:0'
        ]);

        Contract::create($request->all());
        return redirect()->route('contracts.index')->with('success', 'Kontrak pegawai berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        $users = \App\Models\User::where('role', 'pegawai')->get();
        $packages = \App\Models\JobPackage::all();

        return view('contracts.edit', compact('contract', 'users', 'packages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'job_package_id' => 'required|exists:job_packages,id',
            'nama_kontrak' => 'required|string',
            'jabatan' => 'required|string',
            'tujuan' => 'nullable|string',
            'sasaran' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kuota_cuti' => 'required|integer|min:0'
        ]);

        $contract->update($request->all());

        return redirect()->route('contracts.index')->with('success', 'Data kontrak berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect()->route('contracts.index')->with('success', 'Kontrak berhasil dihapus!');
    }
}
