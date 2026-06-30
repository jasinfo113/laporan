<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Http\Requests\StoreLeaveRequest;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    protected LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index()
    {
        $user = Auth::user();
        $tahunSekarang = (int) date('Y');

        // Jika yang login ADMIN atau STAFF: Tampilkan SEMUA data cuti
        if ($user->role === 'admin' || $user->role === 'staff') {
            $leaves = Leave::with('user')
                           ->orderBy('tanggal_cuti', 'desc')
                           ->paginate(10);
            $sisaCuti = null;
        }
        // Jika yang login PEGAWAI: Tampilkan HANYA data miliknya
        else {
            $stats = $user->getLeaveStats($tahunSekarang);
            $sisaCuti = $stats['remaining'];

            $leaves = Leave::where('user_id', $user->id)
                           ->orderBy('tanggal_cuti', 'desc')
                           ->paginate(10);
        }

        return view('leaves.index', compact('leaves', 'sisaCuti', 'tahunSekarang'));
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
