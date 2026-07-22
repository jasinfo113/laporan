<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['name', 'nik', 'jabatan', 'job_package'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'name';
        }

        $query = User::with(['contracts.jobPackage.scopes'])
            ->select('users.*');

        if ($sort === 'job_package') {
            $query->leftJoin('contracts', function ($join) {
                $join->on('users.id', '=', 'contracts.user_id')
                     ->whereDate('contracts.tanggal_mulai', '<=', now())
                     ->whereDate('contracts.tanggal_selesai', '>=', now());
            })
            ->leftJoin('job_packages', 'contracts.job_package_id', '=', 'job_packages.id')
            ->orderBy('job_packages.nama_paket', $direction);
        } elseif ($sort === 'jabatan') {
            $query->leftJoin('contracts', function ($join) {
                $join->on('users.id', '=', 'contracts.user_id')
                     ->whereDate('contracts.tanggal_mulai', '<=', now())
                     ->whereDate('contracts.tanggal_selesai', '>=', now());
            })
            ->orderBy('contracts.jabatan', $direction);
        } else {
            $query->orderBy('users.' . $sort, $direction);
        }

        $users = $query->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                          ->orWhere('users.email', 'like', "%{$search}%")
                          ->orWhere('users.nik', 'like', "%{$search}%")
                          ->orWhereHas('contracts', function ($qc) use ($search) {
                              $qc->where('jabatan', 'like', "%{$search}%")
                                 ->orWhereHas('jobPackage', function ($qp) use ($search) {
                                     $qp->where('nama_paket', 'like', "%{$search}%");
                                 });
                          });
                });
            })
            ->paginate($perPage);

        $columns = [
            [
                'key' => 'name',
                'label' => 'Nama',
                'sortable' => true,
                'render' => function ($u) {
                    return '<div class="flex flex-col">' .
                                '<span class="font-semibold text-gray-800 dark:text-gray-200">' . e($u->name) . '</span>' .
                                '<span class="text-xs text-gray-400 dark:text-gray-500">' . e($u->email) . '</span>' .
                           '</div>';
                }
            ],
            [
                'key' => 'nik',
                'label' => 'NIK',
                'sortable' => true,
                'render' => function ($u) {
                    return e($u->nik ?? '-');
                }
            ],
            [
                'key' => 'jabatan',
                'label' => 'Jabatan',
                'sortable' => true,
                'render' => function ($u) {
                    $activeContract = $u->contracts
                        ->where('tanggal_mulai', '<=', now()->toDateString())
                        ->where('tanggal_selesai', '>=', now()->toDateString())
                        ->first();
                    return e($activeContract->jabatan ?? '-');
                }
            ],
            [
                'key' => 'job_package',
                'label' => 'Paket Pekerjaan',
                'sortable' => true,
                'render' => function ($u) {
                    $activeContract = $u->contracts
                        ->where('tanggal_mulai', '<=', now()->toDateString())
                        ->where('tanggal_selesai', '>=', now()->toDateString())
                        ->first();
                    if ($activeContract && $activeContract->jobPackage) {
                        return '<span class="font-medium text-blue-700 dark:text-blue-400">' . e($activeContract->jobPackage->nama_paket) . '</span><br>' .
                               '<span class="rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">' .
                                    $activeContract->jobPackage->scopes->count() . ' Aktivitas' .
                               '</span>';
                    } else {
                        return '<span class="italic text-gray-400 dark:text-gray-500">Belum diset</span>';
                    }
                }
            ],
            [
                'key' => 'actions',
                'label' => 'Aksi',
                'sortable' => false,
                'align' => 'center',
                'render' => function ($u) {
                    $editUrl = route('users.edit', $u->id);
                    $deleteUrl = route('users.destroy', $u->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    return <<<HTML
                        <div class="flex justify-center gap-2" onclick="event.stopPropagation()">
                            <a href="{$editUrl}" class="inline-flex items-center rounded-lg border p-2 transition border-yellow-200 bg-yellow-50 text-yellow-750 hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50" title="Edit">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </a>
                            <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin mau hapus akun ini?');">
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

        return view('users.index', compact('users', 'columns'));
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
            'role' => 'required|in:admin,pegawai,staff' // Tambah validasi role
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
            'role' => 'required|in:admin,pegawai,staff'
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
