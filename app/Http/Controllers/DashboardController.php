<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\DailyTask;
use Carbon\Carbon;
use App\Models\Contract;
use App\Models\Leave;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $bulanIni = Carbon::now()->month;
            $tahunIni = Carbon::now()->year;
            $hariIni = Carbon::today();

            $data = [
                'totalPegawai' => User::where('role', 'pegawai')->count(),
                'kontrakAktif' => Contract::whereDate('tanggal_mulai', '<=', $hariIni)
                                          ->whereDate('tanggal_selesai', '>=', $hariIni)
                                          ->count(),
                'laporanBulanIni' => Report::where('bulan', $bulanIni)->where('tahun', $tahunIni)->count(),
                'cutiHariIni' => Leave::whereDate('tanggal_cuti', $hariIni)->count(),
                'pegawaiCuti' => Leave::with('user')->whereDate('tanggal_cuti', '>=', $hariIni)
                                          ->orderBy('tanggal_cuti', 'asc')
                                          ->take(5)->get(),
            ];

            return view('dashboard-admin', $data);
        }

        $bulanSekarang = date('n');
        $tahunSekarang = date('Y');

        // 1. DATA KARTU STATISTIK UMUM
        $totalLaporan = Report::where('user_id', $user->id)->count();
        $kegiatanBulanIni = DailyTask::whereHas('report', function($query) use ($user, $bulanSekarang, $tahunSekarang) {
            $query->where('user_id', $user->id)
                  ->where('bulan', $bulanSekarang)
                  ->where('tahun', $tahunSekarang);
        })->count();

        // 2. KONTRAK AKTIF & TARGET AKTIVITAS
        $activeContract = $user->activeContract;
        $targetAktivitas = $activeContract && $activeContract->jobPackage ? $activeContract->jobPackage->scopes()->count() : 0;

        // 3. LOGIKA KALKULATOR CUTI (Tahun Berjalan)
        $totalJatahCuti = $user->contracts()
                               ->whereYear('tanggal_mulai', $tahunSekarang)
                               ->sum('kuota_cuti');

        $cutiTerpakai = $user->leaves()
                             ->whereYear('tanggal_cuti', $tahunSekarang)
                             ->count();

        $sisaCuti = $totalJatahCuti - $cutiTerpakai;

        // 4. DATA 5 AKTIVITAS TERAKHIR
        $recentTasks = DailyTask::with(['scope', 'report'])
            ->whereHas('report', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->orderBy('tanggal', 'desc')->take(5)->get();

        // 5. DATA GRAFIK
        $chartQuery = DailyTask::whereHas('report', function($query) use ($user, $bulanSekarang, $tahunSekarang) {
                $query->where('user_id', $user->id)
                      ->where('bulan', $bulanSekarang)
                      ->where('tahun', $tahunSekarang);
            })
            ->selectRaw('DATE(tanggal) as tgl, count(*) as total')
            ->groupBy('tgl')->orderBy('tgl', 'asc')->get();

        $chartLabels = []; $chartData = [];
        foreach ($chartQuery as $row) {
            $chartLabels[] = Carbon::parse($row->tgl)->format('d M');
            $chartData[] = $row->total;
        }

        return view('dashboard', compact(
            'totalLaporan', 'kegiatanBulanIni', 'targetAktivitas', 'activeContract',
            'totalJatahCuti', 'cutiTerpakai', 'sisaCuti', 'tahunSekarang',
            'recentTasks', 'chartLabels', 'chartData'
        ));
    }
}
