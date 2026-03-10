<?php

namespace App\Http\Controllers;

use App\Models\JobPackage;
use App\Models\Scope;
use App\Models\Approver;
use Illuminate\Http\Request;

class JobPackageController extends Controller
{
    public function index()
    {
        // Ambil data paket pekerjaan beserta jumlah aktivitasnya
        $packages = JobPackage::withCount('scopes')
            ->orderBy('nama_paket')
            ->paginate(10);
        return view('job_packages.index', compact('packages'));
    }

    public function create()
    {
        $approvers = Approver::all();
        return view('job_packages.create', compact('approvers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'approver_id' => 'required|exists:approvers,id',
            'scopes.*.kode_aktivitas' => 'required|string',
            'scopes.*.uraian' => 'required|string',
        ]);

        // 1. Simpan Header (Nama Paket)
        $package = JobPackage::create([
            'nama_paket' => $request->nama_paket,
            'approver_id' => $request->approver_id,
        ]);

        // 2. Simpan Detail (Daftar Aktivitas)
        if ($request->has('scopes')) {
            $package->scopes()->createMany($request->scopes);
        }

        return redirect()->route('job_packages.index')->with('success', 'Paket Pekerjaan dan Aktivitas berhasil disimpan!');
    }

    public function edit(JobPackage $jobPackage)
    {
        $jobPackage->load('scopes');
        $approvers = Approver::all();
        return view('job_packages.edit', compact('jobPackage', 'approvers'));
    }

    public function update(Request $request, JobPackage $jobPackage)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'approver_id' => 'required|exists:approvers,id',
            'scopes.*.id' => 'nullable|exists:scopes,id',
            'scopes.*.kode_aktivitas' => 'required|string',
            'scopes.*.uraian' => 'required|string',
        ]);

        // 1. Update Header
        $jobPackage->update([
            'nama_paket' => $request->nama_paket,
            'approver_id' => $request->approver_id
        ]);

        // 2. Update Detail (Aktivitas)
        $submittedScopeIds = []; // Untuk melacak ID apa saja yang disubmit form

        if ($request->has('scopes')) {
            foreach ($request->scopes as $scopeData) {
                if (isset($scopeData['id'])) {
                    // Jika ada ID-nya, berarti ini data lama yang di-edit
                    $scope = Scope::find($scopeData['id']);
                    $scope->update($scopeData);
                    $submittedScopeIds[] = $scope->id;
                } else {
                    // Jika tidak ada ID-nya, berarti ini baris baru yang ditambahkan
                    $newScope = $jobPackage->scopes()->create($scopeData);
                    $submittedScopeIds[] = $newScope->id;
                }
            }
        }

        // 3. Hapus aktivitas lama yang dibuang/dihapus (silang merah) dari form
        $jobPackage->scopes()->whereNotIn('id', $submittedScopeIds)->delete();

        return redirect()->route('job_packages.index')->with('success', 'Paket Pekerjaan berhasil diperbarui!');
    }

    public function destroy(JobPackage $jobPackage)
    {
        $jobPackage->delete();
        return redirect()->route('job_packages.index')->with('success', 'Paket Pekerjaan berhasil dihapus!');
    }
}
