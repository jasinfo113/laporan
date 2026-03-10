<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tahunSekarang = date('Y');

        // Jika yang login ADMIN: Tampilkan SEMUA data cuti
        if ($user->role === 'admin') {
            $leaves = Leave::with('user') // Ambil relasi user biar bisa nampilin nama
                           ->orderBy('tanggal_cuti', 'desc')
                           ->paginate(10);
            $sisaCuti = null; // Admin gak butuh sisa cuti di halaman ini
        }
        // Jika yang login PEGAWAI: Tampilkan HANYA data miliknya
        else {
            $totalJatahCuti = $user->contracts()->whereYear('tanggal_mulai', $tahunSekarang)->sum('kuota_cuti');
            $cutiTerpakai = $user->leaves()->whereYear('tanggal_cuti', $tahunSekarang)->count();
            $sisaCuti = $totalJatahCuti - $cutiTerpakai;

            $leaves = Leave::where('user_id', $user->id)
                           ->orderBy('tanggal_cuti', 'desc')
                           ->paginate(10);
        }

        return view('leaves.index', compact('leaves', 'sisaCuti', 'tahunSekarang'));
    }

    public function create()
    {
        $user = Auth::user();
        $tahunSekarang = date('Y');

        // Hitung sisa cuti untuk ditampilkan di form
        $totalJatahCuti = $user->contracts()->whereYear('tanggal_mulai', $tahunSekarang)->sum('kuota_cuti');
        $cutiTerpakai = $user->leaves()->whereYear('tanggal_cuti', $tahunSekarang)->count();
        $sisaCuti = $totalJatahCuti - $cutiTerpakai;

        return view('leaves.create', compact('sisaCuti', 'tahunSekarang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_cuti' => 'required|date',
            'keterangan' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $tahunCuti = Carbon::parse($request->tanggal_cuti)->year;

        // LOGIKA PENJAGA: Cek apakah jatah cuti di tahun tersebut masih ada
        $totalJatahCuti = $user->contracts()->whereYear('tanggal_mulai', $tahunCuti)->sum('kuota_cuti');
        $cutiTerpakai = $user->leaves()->whereYear('tanggal_cuti', $tahunCuti)->count();
        $sisaCuti = $totalJatahCuti - $cutiTerpakai;

        if ($sisaCuti <= 0) {
            return back()->with('error', "Pengajuan gagal! Jatah cuti kamu untuk tahun {$tahunCuti} sudah habis.");
        }

        // Cek jangan sampai input cuti di tanggal yang sama dua kali
        $sudahCuti = Leave::where('user_id', $user->id)
                          ->whereDate('tanggal_cuti', $request->tanggal_cuti)
                          ->exists();

        if ($sudahCuti) {
            return back()->with('error', 'Kamu sudah mengajukan cuti di tanggal tersebut!');
        }

        Leave::create([
            'user_id' => $user->id,
            'tanggal_cuti' => $request->tanggal_cuti,
            'keterangan' => $request->keterangan,
        ]);

        $tanggal = Carbon::parse($request->tanggal_cuti);
        $bulan = $tanggal->month;
        $tahun = $tanggal->year;

        $report = Report::where('user_id', $user->id)
                        ->where('bulan', $bulan)
                        ->where('tahun', $tahun)
                        ->first();
        if (!$report) {
            $activeContract = $user->contracts()
                ->whereDate('tanggal_mulai', '<=', $request->tanggal_cuti)
                ->whereDate('tanggal_selesai', '>=', $request->tanggal_cuti)
                ->first();

            if($activeContract) {
                $report = Report::create([
                    'user_id' => $user->id,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'contract_id' => $activeContract->id
                ]);
            }
        }

        if($report) {
            $report->dailyTasks()->create([
                'scope_id' => null, // Cuti tidak punya scope
                'tanggal' => $request->tanggal_cuti,
                'deskripsi_pekerjaan' => 'Cuti: ' . $request->keterangan,
            ]);
        }

        return redirect()->route('leaves.index')->with('success', 'Pengajuan cuti berhasil! Sisa cuti kamu otomatis berkurang.');
    }

    public function destroy(Leave $leaf)
    {
        // Pastikan hanya pemiliknya ATAU ADMIN yang bisa batalin cuti
        if ($leaf->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $leaf->delete();

        return redirect()->route('leaves.index')->with('success', 'Data cuti berhasil dibatalkan/dihapus.');
    }
}
