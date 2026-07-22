<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Http\Requests\StoreLeaveRequest;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $tahunSekarang = (int) date('Y');
        $isAdminOrStaff = ($user->role === 'admin' || $user->role === 'staff');

        $search = $request->input('search');
        $sort = $request->input('sort', 'tanggal_cuti');
        $direction = $request->input('direction', 'desc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['pegawai', 'tanggal_cuti', 'keterangan'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'tanggal_cuti';
        }

        $query = Leave::with('user');

        if (!$isAdminOrStaff) {
            $query->where('user_id', $user->id);
            $stats = $user->getLeaveStats($tahunSekarang);
            $sisaCuti = $stats['remaining'];
        } else {
            $sisaCuti = null;
            // Join users to allow sorting / searching on name
            $query->select('leaves.*')
                  ->join('users', 'leaves.user_id', '=', 'users.id');
        }

        $query->when($search, function ($q) use ($search, $isAdminOrStaff) {
            $q->where(function ($inner) use ($search, $isAdminOrStaff) {
                $inner->where('leaves.keterangan', 'like', "%{$search}%")
                      ->orWhere('leaves.tanggal_cuti', 'like', "%{$search}%");
                if ($isAdminOrStaff) {
                    $inner->orWhere('users.name', 'like', "%{$search}%");
                }
            });
        });

        if ($isAdminOrStaff && $sort === 'pegawai') {
            $query->orderBy('users.name', $direction);
        } else {
            // Qualify the column name to avoid SQL ambiguity
            $query->orderBy('leaves.' . $sort, $direction);
        }

        $leaves = $query->paginate($perPage);

        // Columns definition
        $columns = [];
        if ($isAdminOrStaff) {
            $columns[] = [
                'key' => 'pegawai',
                'label' => 'Nama Pegawai',
                'sortable' => true,
                'render' => function ($leave) {
                    return '<span class="font-semibold text-gray-800 dark:text-gray-100">' . e($leave->user->name ?? 'Tidak Diketahui') . '</span>';
                }
            ];
        }

        $columns[] = [
            'key' => 'tanggal_cuti',
            'label' => 'Tanggal Cuti',
            'sortable' => true,
            'render' => function ($leave) {
                return '<span class="font-semibold text-gray-800 dark:text-gray-100">' . 
                       e(\Carbon\Carbon::parse($leave->tanggal_cuti)->locale('id')->isoFormat('dddd, D MMMM Y')) . 
                       '</span>';
            }
        ];

        $columns[] = [
            'key' => 'keterangan',
            'label' => 'Keterangan',
            'sortable' => true,
        ];

        $columns[] = [
            'key' => 'actions',
            'label' => 'Aksi',
            'sortable' => false,
            'render' => function ($leave) {
                $deleteUrl = route('leaves.destroy', $leave->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');
                return <<<HTML
                    <div onclick="event.stopPropagation()">
                        <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan/menghapus cuti ini?');">
                            {$csrf}
                            {$method}
                            <button type="submit" class="inline-flex items-center rounded-lg border p-2 transition border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50" title="Batal">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>
                    </div>
HTML;
            }
        ];

        return view('leaves.index', compact('leaves', 'sisaCuti', 'tahunSekarang', 'columns'));
    }

    public function create()
    {
        $user = Auth::user();
        $tahunSekarang = (int) date('Y');

        $stats = $user->getLeaveStats($tahunSekarang);
        $sisaCuti = $stats['remaining'];

        return view('leaves.create', compact('sisaCuti', 'tahunSekarang'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $user = Auth::user();

        try {
            $this->leaveService->applyForLeave($user, $request->tanggal_cuti, $request->keterangan);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('leaves.index')->with('success', 'Pengajuan cuti berhasil! Sisa cuti kamu otomatis berkurang.');
    }

    public function destroy(Leave $leaf)
    {
        $user = Auth::user();

        // Pastikan hanya pemiliknya ATAU ADMIN/STAFF yang bisa batalin cuti
        if ($leaf->user_id !== $user->id && $user->role !== 'admin' && $user->role !== 'staff') {
            abort(403, 'Akses ditolak.');
        }

        try {
            $this->leaveService->cancelLeave($leaf);
        } catch (\Throwable $e) {
            return redirect()->route('leaves.index')->with('error', 'Gagal membatalkan cuti: ' . $e->getMessage());
        }

        return redirect()->route('leaves.index')->with('success', 'Data cuti berhasil dibatalkan/dihapus.');
    }
}
