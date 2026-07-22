<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use Illuminate\Http\Request;

class ApproverController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'nama');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['nama', 'nip', 'jabatan'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'nama';
        }

        $approvers = Approver::when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage);

        $columns = [
            [
                'key' => 'nama',
                'label' => 'Nama',
                'sortable' => true,
            ],
            [
                'key' => 'nip',
                'label' => 'NIP',
                'sortable' => true,
            ],
            [
                'key' => 'jabatan',
                'label' => 'Jabatan',
                'sortable' => true,
            ],
            [
                'key' => 'actions',
                'label' => 'Aksi',
                'sortable' => false,
                'render' => function ($ap) {
                    $editUrl = route('approvers.edit', $ap->id);
                    $deleteUrl = route('approvers.destroy', $ap->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    return <<<HTML
                        <div class="flex gap-2" onclick="event.stopPropagation()">
                            <a href="{$editUrl}" class="inline-flex items-center rounded-lg border p-2 transition border-yellow-200 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </a>
                            <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin hapus pejabat ini?');">
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

        return view('approvers.index', compact('approvers', 'columns'));
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
