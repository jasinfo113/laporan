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
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'tanggal_mulai');
        $direction = $request->input('direction', 'desc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['pegawai', 'nama_kontrak', 'tanggal_mulai'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'tanggal_mulai';
        }

        $contracts = Contract::with(['user', 'jobPackage'])
            ->select('contracts.*')
            ->join('users', 'contracts.user_id', '=', 'users.id')
            ->join('job_packages', 'contracts.job_package_id', '=', 'job_packages.id')
            ->when($search, function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                      ->orWhere('contracts.nama_kontrak', 'like', "%{$search}%")
                      ->orWhere('job_packages.nama_paket', 'like', "%{$search}%");
            })
            ->orderBy($sort === 'pegawai' ? 'users.name' : $sort, $direction)
            ->paginate($perPage);

        $columns = [
            [
                'key' => 'pegawai',
                'label' => 'Pegawai',
                'sortable' => true,
                'render' => function ($c) {
                    return '<span class="font-bold text-gray-800 dark:text-gray-200">' . e($c->user->name) . '</span>';
                }
            ],
            [
                'key' => 'nama_kontrak',
                'label' => 'Nama Kontrak',
                'sortable' => true,
                'render' => function ($c) {
                    return e($c->nama_kontrak) . '<br><span class="text-xs text-gray-500 dark:text-gray-400">' . e($c->jobPackage->nama_paket) . '</span>';
                }
            ],
            [
                'key' => 'tanggal_mulai',
                'label' => 'Masa Berlaku',
                'sortable' => true,
                'render' => function ($c) {
                    return \Carbon\Carbon::parse($c->tanggal_mulai)->format('d M Y') . ' s/d ' . \Carbon\Carbon::parse($c->tanggal_selesai)->format('d M Y');
                }
            ],
            [
                'key' => 'actions',
                'label' => 'Aksi',
                'sortable' => false,
                'render' => function ($c) {
                    $editUrl = route('contracts.edit', $c->id);
                    $deleteUrl = route('contracts.destroy', $c->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    return <<<HTML
                        <div class="flex items-center gap-3" onclick="event.stopPropagation()">
                            <a href="{$editUrl}" class="inline-flex items-center rounded-lg border p-2 transition border-yellow-200 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </a>
                            <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin hapus kontrak ini?');">
                                {$csrf}
                                {$method}
                                <button type="submit" class="inline-flex items-center rounded-lg border p-2 transition border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50" title="Hapus">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V5h6v2m-7 4v6m4-6v6m4-10v12a1 1 0 01-1 1H9a1 1 0 01-1-1V7h8z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
HTML;
                }
            ]
        ];

        return view('contracts.index', compact('contracts', 'columns'));
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
