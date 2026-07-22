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
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $bulanIni = Carbon::now()->month;
            $tahunIni = Carbon::now()->year;
            $hariIni = Carbon::today();

            $search = $request->input('search');
            $sort = $request->input('sort', 'tanggal_cuti');
            $direction = $request->input('direction', 'asc');
            $perPage = (int) $request->input('perPage', 5);

            $sortableKeys = ['pegawai', 'tanggal_cuti', 'keterangan'];
            if (!in_array($sort, $sortableKeys)) {
                $sort = 'tanggal_cuti';
            }

            $query = Leave::with('user')
                ->whereDate('tanggal_cuti', '>=', $hariIni);

            if ($sort === 'pegawai') {
                $query->select('leaves.*')
                      ->join('users', 'leaves.user_id', '=', 'users.id')
                      ->orderBy('users.name', $direction);
            } else {
                $query->orderBy('leaves.' . $sort, $direction);
            }

            if ($search) {
                if ($sort !== 'pegawai') {
                    $query->select('leaves.*')
                          ->join('users', 'leaves.user_id', '=', 'users.id');
                }
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', "%{$search}%")
                      ->orWhere('leaves.keterangan', 'like', "%{$search}%")
                      ->orWhere('leaves.tanggal_cuti', 'like', "%{$search}%");
                });
            }

            $pegawaiCuti = $query->paginate($perPage);

            $columns = [
                [
                    'key' => 'pegawai',
                    'label' => 'Nama Pegawai',
                    'sortable' => true,
                    'render' => function ($cuti) {
                        return '<span class="font-semibold text-gray-800 dark:text-gray-100">' . e($cuti->user->name) . '</span>';
                    }
                ],
                [
                    'key' => 'tanggal_cuti',
                    'label' => 'Tanggal Cuti',
                    'sortable' => true,
                    'render' => function ($cuti) {
                        $isToday = \Carbon\Carbon::parse($cuti->tanggal_cuti)->isToday();
                        $badge = $isToday ? ' <span class="ml-2 bg-rose-100 text-rose-700 text-xs font-bold px-2 py-1 rounded dark:bg-rose-900/40 dark:text-rose-300">Hari Ini</span>' : '';
                        return '<span class="font-mono text-xs text-gray-600 dark:text-gray-300">' . 
                               e(\Carbon\Carbon::parse($cuti->tanggal_cuti)->locale('id')->isoFormat('D MMMM Y')) . 
                               '</span>' . $badge;
                    }
                ],
                [
                    'key' => 'keterangan',
                    'label' => 'Keterangan',
                    'sortable' => true,
                ]
            ];

            $data = [
                'totalPegawai' => User::where('role', 'pegawai')->count(),
                'kontrakAktif' => Contract::whereDate('tanggal_mulai', '<=', $hariIni)
                                          ->whereDate('tanggal_selesai', '>=', $hariIni)
                                          ->count(),
                'laporanBulanIni' => Report::where('bulan', $bulanIni)->where('tahun', $tahunIni)->count(),
                'cutiHariIni' => Leave::whereDate('tanggal_cuti', $hariIni)->count(),
                'pegawaiCuti' => $pegawaiCuti,
                'columns' => $columns,
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
        $stats = $user->getLeaveStats($tahunSekarang);
        $totalJatahCuti = $stats['total'];
        $cutiTerpakai = $stats['used'];
        $sisaCuti = $stats['remaining'];

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
