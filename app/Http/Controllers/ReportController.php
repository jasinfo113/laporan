<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Http\Requests\StoreReportRequest;
use App\Services\DocumentExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected DocumentExportService $exportService;

    public function __construct(DocumentExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    // Menampilkan daftar laporan
    public function index(Request $request)
    {
        $user = Auth::user();
        $canViewAllReports = $user->role === 'staff';

        $search = $request->input('search');
        $sort = $request->input('sort', 'tahun');
        $direction = $request->input('direction', 'desc');
        $perPage = (int) $request->input('perPage', 10);

        // Bound sorting keys
        $sortableKeys = ['pegawai', 'bulan', 'tahun'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'tahun';
        }

        $query = Report::with('user');

        if (!$canViewAllReports) {
            $query->where('user_id', $user->id);
        } else {
            // Join users to allow sorting / searching on employee name
            $query->select('reports.*')
                  ->join('users', 'reports.user_id', '=', 'users.id');
        }

        $query->when($search, function ($q) use ($search, $canViewAllReports) {
            $q->where(function ($inner) use ($search, $canViewAllReports) {
                $inner->where('reports.tahun', 'like', "%{$search}%")
                      ->orWhere('reports.bulan', 'like', "%{$search}%");
                if ($canViewAllReports) {
                    $inner->orWhere('users.name', 'like', "%{$search}%");
                }
            });
        });

        if ($canViewAllReports && $sort === 'pegawai') {
            $query->orderBy('users.name', $direction);
        } else {
            $query->orderBy('reports.' . $sort, $direction);
            if ($sort === 'tahun') {
                $query->orderBy('reports.bulan', $direction);
            }
        }

        $reports = $query->paginate($perPage);

        $columns = [];
        if ($canViewAllReports) {
            $columns[] = [
                'key' => 'pegawai',
                'label' => 'Pegawai',
                'sortable' => true,
                'render' => function ($report) {
                    return '<span class="font-semibold text-gray-800 dark:text-gray-150">' . e($report->user->name ?? '-') . '</span>';
                }
            ];
        }

        $columns[] = [
            'key' => 'bulan',
            'label' => 'Bulan',
            'sortable' => true,
            'render' => function ($report) {
                return e(date('F', mktime(0, 0, 0, $report->bulan, 10)));
            }
        ];

        $columns[] = [
            'key' => 'tahun',
            'label' => 'Tahun',
            'sortable' => true,
        ];

        $columns[] = [
            'key' => 'actions',
            'label' => 'Aksi',
            'sortable' => false,
            'render' => function ($report) {
                $showUrl = route('reports.show', $report->id);
                return <<<HTML
                    <div onclick="event.stopPropagation()">
                        <a href="{$showUrl}" class="inline-flex items-center gap-1.5 rounded-lg border p-2 transition border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50" title="Buka & Isi">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="text-xs font-semibold">Buka & Isi</span>
                        </a>
                    </div>
HTML;
            }
        ];

        return view('reports.index', compact('reports', 'columns'));
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

    public function show(Request $request, Report $report)
    {
        $user = Auth::user();
        $canManageReport = $report->user_id === $user->id;

        if (!$canManageReport && !$this->canViewAllReports($user)) {
            abort(403);
        }

        $report->load(['user', 'contract.jobPackage']);
        $scopes = $report->contract && $report->contract->jobPackage ? $report->contract->jobPackage->scopes : collect();

        $search = $request->input('search');
        $sort = $request->input('sort', 'tanggal');
        $direction = $request->input('direction', 'asc');
        
        // Handle limit or perPage
        $limit = $request->input('limit', $request->input('perPage', 10));
        $perPage = $limit === 'all' ? 1000 : (int) $limit;

        // Bound sorting keys
        $sortableKeys = ['tanggal', 'scope', 'deskripsi_pekerjaan'];
        if (!in_array($sort, $sortableKeys)) {
            $sort = 'tanggal';
        }

        $query = $report->dailyTasks()
            ->with(['scope', 'taskImages']);

        if ($sort === 'scope') {
            $query->select('daily_tasks.*')
                  ->leftJoin('scopes', 'daily_tasks.scope_id', '=', 'scopes.id')
                  ->orderBy('scopes.kode_aktivitas', $direction);
        } else {
            $query->orderBy('daily_tasks.' . $sort, $direction);
        }

        $query->when($search, function ($q) use ($search) {
            $q->where(function ($inner) use ($search) {
                $inner->where('daily_tasks.deskripsi_pekerjaan', 'like', "%{$search}%")
                      ->orWhere('scopes.kode_aktivitas', 'like', "%{$search}%")
                      ->orWhere(DB::raw('DATE_FORMAT(daily_tasks.tanggal, "%d %M %Y")'), 'like', "%{$search}%");
            });
        });

        $dailyTasks = $query->paginate($perPage)->withQueryString();

        $columns = [
            [
                'key' => 'tanggal',
                'label' => 'Tanggal',
                'sortable' => true,
                'render' => function ($task) {
                    return e(\Carbon\Carbon::parse($task->tanggal)->format('d M Y'));
                }
            ],
            [
                'key' => 'scope',
                'label' => 'Aktivitas',
                'sortable' => true,
                'render' => function ($task) {
                    return e($task->scope ? $task->scope->kode_aktivitas : '-');
                }
            ],
            [
                'key' => 'deskripsi_pekerjaan',
                'label' => 'Deskripsi',
                'sortable' => true,
                'render' => function ($task) {
                    return e($task->deskripsi_pekerjaan);
                }
            ],
            [
                'key' => 'task_images',
                'label' => 'Foto / Bukti',
                'sortable' => false,
                'render' => function ($task) use ($canManageReport) {
                    if ($task->taskImages->count() === 0) {
                        return '<span class="text-gray-400">Tidak ada foto</span>';
                    }
                    $imagesHTML = '<div class="flex flex-wrap gap-2">';
                    foreach ($task->taskImages as $image) {
                        $assetUrl = asset('storage/' . $image->image_path);
                        $escapedCaption = addslashes(e($task->deskripsi_pekerjaan));
                        $roundedClass = $canManageReport ? 'rounded-t' : 'rounded';
                        $imagesHTML .= <<<HTML
                            <div class="w-20 rounded-xl" onclick="event.stopPropagation()">
                                <button
                                    type="button"
                                    @click="
                                        activeImage = '{$assetUrl}';
                                        activeCaption = '{$escapedCaption}';
                                        imageModalOpen = true;
                                    "
                                    class="block focus:outline-none"
                                >
                                    <img src="{$assetUrl}" class="h-12 w-20 {$roundedClass} object-cover shadow-xs transition hover:scale-105" alt="Bukti"/>
                                </button>
HTML;
                        if ($canManageReport) {
                            $destroyUrl = route('task-images.destroy', $image->id);
                            $csrf = csrf_field();
                            $method = method_field('DELETE');
                            $imagesHTML .= <<<HTML
                                <form action="{$destroyUrl}" method="POST" onsubmit="return confirm('Hapus foto bukti ini saja?');" class="m-0 p-0">
                                    {$csrf}
                                    {$method}
                                    <button
                                        type="submit"
                                        class="block w-full bg-red-600 px-2 py-1 text-center text-xs font-bold leading-none rounded-b text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400"
                                    >
                                        hapus
                                    </button>
                                </form>
HTML;
                        }
                        $imagesHTML .= '</div>';
                    }
                    $imagesHTML .= '</div>';
                    return $imagesHTML;
                }
            ]
        ];

        if ($canManageReport) {
            $columns[] = [
                'key' => 'actions',
                'label' => 'Aksi',
                'sortable' => false,
                'align' => 'center',
                'render' => function ($task) {
                    $taskData = [
                        'action' => route('tasks.update', $task->id),
                        'tanggal' => \Carbon\Carbon::parse($task->tanggal)->format('Y-m-d'),
                        'scope_id' => $task->scope_id ?? '',
                        'deskripsi_pekerjaan' => $task->deskripsi_pekerjaan,
                    ];
                    $escapedTask = htmlspecialchars(json_encode($taskData), ENT_QUOTES, 'UTF-8');
                    $csrf = csrf_field();
                    $method = method_field('DELETE');
                    $deleteUrl = route('tasks.destroy', $task->id);
                    return <<<HTML
                        <div class="flex justify-center gap-2" onclick="event.stopPropagation()">
                            <button
                                type="button"
                                @click="openEditTask({$escapedTask})"
                                class="inline-flex items-center rounded-lg border p-2 transition border-yellow-200 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:border-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 dark:hover:bg-yellow-900/50"
                                title="Edit"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M5 19h14M7 16l9-9 2 2-9 9H7v-2z"/>
                                </svg>
                            </button>
                            <form action="{$deleteUrl}" method="POST" onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?');">
                                {$csrf}
                                {$method}
                                <button
                                    type="submit"
                                    class="inline-flex items-center rounded-lg border p-2 transition border-red-200 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50"
                                    title="Hapus"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
HTML;
                }
            ];
        }

        return view('reports.show', compact('report', 'scopes', 'dailyTasks', 'canManageReport', 'columns'));
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
