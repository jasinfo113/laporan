<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use Illuminate\Http\Request;

class ApproverController extends Controller
{
    public function index()
    {
        $approvers = Approver::orderBy('nama')->paginate(10);
        return view('approvers.index', compact('approvers'));
    }

    public function create()
    {
        return view('approvers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        Approver::create($request->all());
        return redirect()->route('approvers.index')->with('success', 'Pejabat berhasil ditambahkan!');
    }

    public function edit(Approver $approver)
    {
        return view('approvers.create', compact('approver'));
    }

    public function update(Request $request, Approver $approver)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        $approver->update($request->all());
        return redirect()->route('approvers.index')->with('success', 'Data Pejabat berhasil diupdate!');
    }

    public function destroy(Approver $approver)
    {
        $approver->delete();
        return redirect()->route('approvers.index')->with('success', 'Pejabat berhasil dihapus!');
    }
}
