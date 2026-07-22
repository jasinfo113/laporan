<?php

namespace App\Http\Controllers;

use App\Models\JobPackage;
use App\Models\Scope;
use App\Models\Approver;
use Illuminate\Http\Request;

class JobPackageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'nama_paket');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['nama_paket', 'scopes_count'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'nama_paket';
        }

        $packages = JobPackage::withCount('scopes')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_paket', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        $columns = [
            [
                'key' => 'nama_paket',
                'label' => 'Nama Paket Pekerjaan',
                'sortable' => true,
            ],
            [
                'key' => 'scopes_count',
                'label' => 'Jumlah Aktivitas (Scope)',
                'sortable' => true,
                'align' => 'center',
                'render' => function ($package) {
                    return '<span class="rounded bg-blue-100 px-2.5 py-0.5 font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">' . $package->scopes_count . '</span>';
                }
            ],
            [
                'key' => 'actions',
                'label' => 'Aksi',
                'sortable' => false,
                'align' => 'center',
                'render' => function ($package) {
                    $editUrl = route('job_packages.edit', $package->id);
                    $deleteUrl = route('job_packages.destroy', $package->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    return <<<HTML
                        <div class="flex justify-center gap-2" onclick="event.stopPropagation()">
                            <a href="{$editUrl}" class="inline-flex items-center rounded-lg border p-2 transition border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50" title="Detail / Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </a>
                            <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin hapus paket ini?');">
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

        return view('job_packages.index', compact('packages', 'columns'));
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
