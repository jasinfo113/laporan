<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Http\Requests\StoreReportRequest;
use App\Services\DocumentExportService;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected DocumentExportService $exportService;

    public function __construct(DocumentExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    // Menampilkan daftar laporan
    public function index()
    {
        $user = Auth::user();
        $reports = Report::with('user')
                        ->when(!$this->canViewAllReports($user), function ($query) use ($user) {
                            $query->where('user_id', $user->id);
                        })
                        ->orderBy('tahun', 'desc')
                        ->orderBy('bulan', 'desc')
                        ->paginate(10);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(StoreReportRequest $request)
    {
        $user = Auth::user();

        // 1. CEK KONTRAK AKTIF
        // Laporan hanya bisa dibuat kalau pegawai punya kontrak di bulan & tahun tersebut
        $activeContract = $user->getActiveContractForPeriod($request->bulan, $request->tahun);

        if (!$activeContract) {
            return back()->with('error', 'Gagal membuat laporan: Kamu belum memiliki kontrak kerja yang aktif pada periode tersebut!');
        }

        $exists = Report::where('user_id', $user->id)
                        ->where('bulan', $request->bulan)
                        ->where('tahun', $request->tahun)
                        ->exists();

        if ($exists) {
            return back()->with('error', 'Laporan untuk bulan dan tahun tersebut sudah ada!');
        }

        // 2. SIMPAN REPORT DENGAN ID KONTRAK
        $report = Report::create([
            'user_id' => $user->id,
            'contract_id' => $activeContract->id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ]);

        return redirect()->route('reports.show', $report->id)
                         ->with('success', 'Laporan bulan baru berhasil dibuat!');
    }

    public function show(Report $report)
    {
        $user = Auth::user();
        $canManageReport = $report->user_id === $user->id;

        if (!$canManageReport && !$this->canViewAllReports($user)) {
            abort(403);
        }

        $report->load(['user', 'contract.jobPackage']);
        $scopes = $report->contract && $report->contract->jobPackage ? $report->contract->jobPackage->scopes : collect();
        $limit = request('limit', 10);
        $query = $report->dailyTasks()
            ->with(['scope', 'taskImages'])
            ->orderBy('tanggal', 'asc');

        if ($limit === 'all') {
            $dailyTasks = $query->get();
        } else {
            $dailyTasks = $query->paginate((int) $limit)->withQueryString();
        }

        return view('reports.show', compact('report', 'scopes', 'dailyTasks', 'canManageReport'));
    }

    public function exportWord(Report $report)
    {
        $user = Auth::user();

        // Pemilik laporan dan staff boleh melihat/mencetak laporan.
        if ($report->user_id !== $user->id && !$this->canViewAllReports($user)) {
            abort(403);
        }

        try {
            $filePath = $this->exportService->exportReportToWord($report);
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);

            $message = config('app.debug')
                ? $e->getMessage() . ' di baris ' . $e->getLine()
                : 'Gagal mengekspor laporan Word. Silakan coba lagi.';

            return back()->with('error', $message);
        }
    }

    private function canViewAllReports($user): bool
    {
        return $user && $user->role === 'staff';
    }
}
