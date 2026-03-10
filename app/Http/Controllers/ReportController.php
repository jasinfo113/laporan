<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Menampilkan daftar laporan
    public function index()
    {
        $reports = Report::where('user_id', Auth::id())
                        ->orderBy('tahun', 'desc')
                        ->orderBy('bulan', 'desc')
                        ->paginate(10);
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020'
        ]);

        $user = Auth::user();

        // 1. CEK KONTRAK AKTIF
        // Laporan hanya bisa dibuat kalau pegawai punya kontrak di bulan & tahun tersebut
        // (Untuk simplifikasi, kita ambil kontrak yang sedang aktif saat laporan dibuat)
        $activeContract = $user->activeContract;

        if (!$activeContract) {
            return back()->with('error', 'Gagal membuat laporan: Kamu belum memiliki kontrak kerja yang aktif saat ini!');
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
            'contract_id' => $activeContract->id, // <--- Relasi baru!
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ]);

        return redirect()->route('reports.show', $report->id)
                         ->with('success', 'Laporan bulan baru berhasil dibuat!');
    }

    // Halaman detail laporan (nanti untuk input foto & aktivitas)
    public function show(Report $report)
    {
        if ($report->user_id !== Auth::id()) {
            abort(403);
        }

        $report->load(['contract.jobPackage']);

        // Scopes diambil dari Kontrak, bukan dari User lagi
        $scopes = $report->contract && $report->contract->jobPackage ? $report->contract->jobPackage->scopes : collect();

        $dailyTasks = $report->dailyTasks()
            ->with(['scope', 'taskImages'])
            ->orderBy('tanggal')
            ->paginate(10);

        return view('reports.show', compact('report', 'scopes', 'dailyTasks'));
    }

    public function exportWord(Report $report)
    {
        try {
            Settings::setOutputEscapingEnabled(true);
            // Pastikan keamanan: hanya user pemilik laporan yang bisa cetak
            if ($report->user_id !== Auth::id()) {
                abort(403);
            }

            // Load semua relasi
            $report->load(['contract.jobPackage.approver', 'dailyTasks.scope', 'dailyTasks.taskImages']);
            $kontrak = $report->contract; // Shortcut variabel kontrak
            if (!$kontrak) {
                return back()->with('error', 'Data kontrak tidak ditemukan pada laporan ini.');
            }

            // 1. Panggil file template
            $templatePath = resource_path('templates/template_laporan.docx');
            if (!file_exists($templatePath)) {
                return back()->with('error', 'File template_laporan.docx tidak ditemukan di folder templates!');
            }

            $template = new TemplateProcessor($templatePath);

            // 2. Replace Variabel Header/Biodata
            $namaBulan = Carbon::createFromDate($report->tahun, $report->bulan, 1)->locale('id')->isoFormat('MMMM');
            $tanggalLaporan = Carbon::create($report->tahun, $report->bulan, 1)->addMonth();
            if ($tanggalLaporan->isWeekend()) {
                $tanggalLaporan->next(Carbon::MONDAY);
            }

            $template->setValue('bulan', $namaBulan);
            $template->setValue('tahun', $report->tahun);
            $template->setValue('tanggal_laporan', $tanggalLaporan->locale('id')->isoFormat('D MMMM Y'));

            $template->setValue('nama_pegawai', $report->user->name);
            $template->setValue('nik', $report->user->nik ?? '-');

            $template->setValue('spk_nomor', $kontrak->spk_nomor ?? '-');
            $template->setValue('spk_tanggal', $kontrak->spk_tanggal ? Carbon::parse($kontrak->spk_tanggal)->format('d F Y') : '-');
            $template->setValue('spmk_nomor', $kontrak->spmk_nomor ?? '-');
            $template->setValue('spmk_tanggal', $kontrak->spmk_tanggal ? Carbon::parse($kontrak->spmk_tanggal)->format('d F Y') : '-');
            $template->setValue('jabatan', $kontrak->jabatan);
            $template->setValue('nama_kontrak', $kontrak->nama_kontrak);

            // --- JURUS BARU: Parsing Teks ke Array Block buat Numbering ---
            $siapinListWord = function($teks, $variabelTeks) {
                $baris = explode("\n", $teks ?? '-');
                $hasil = [];

                foreach ($baris as $b) {
                    $bersih = trim($b);
                    if (!empty($bersih)) {
                        // Cerdas: Hapus angka "1. ", "2. " atau strip "- " dari ketikan Admin
                        // Biar nggak bentrok/dobel sama auto-numbering dari Ms. Word
                        $bersih = preg_replace('/^(\d+\.|\-)\s*/', '', $bersih);
                        $hasil[] = [$variabelTeks => $bersih];
                    }
                }

                // Kalau admin ngosongin isiannya
                if (empty($hasil)) {
                    $hasil[] = [$variabelTeks => '-'];
                }

                return $hasil;
            };

            // Lempar ke Word pakai cloneBlock (Bukan setComplexValue lagi)
            // Format parameter: cloneBlock(nama_blok, jumlah_clone (0=otomatis), replace, indexVariables, array_data)
            $template->cloneBlock('block_tujuan', 0, true, false, $siapinListWord($kontrak->tujuan, 'teks_tujuan'));
            $template->cloneBlock('block_sasaran', 0, true, false, $siapinListWord($kontrak->sasaran, 'teks_sasaran'));
            $template->cloneBlock('block_ruang_lingkup', 0, true, false, $siapinListWord($kontrak->ruang_lingkup, 'teks_ruang_lingkup'));


            $approver = $kontrak->jobPackage ? $kontrak->jobPackage->approver : null;
            $template->setValue('nama_pejabat', $approver ? $approver->nama : 'Belum Diset');
            $template->setValue('nip_pejabat', $approver ? $approver->nip : '-');
            $template->setValue('jabatan_pejabat', $approver ? $approver->jabatan : '-');

            // --- PROSES TABEL TARGET & REALISASI ---
            $scopes = $kontrak->jobPackage ? $kontrak->jobPackage->scopes : collect();
            $jumlahScope = $scopes->count();

            if ($jumlahScope > 0) {
                $template->cloneRow('aktifitas', $jumlahScope);
                $template->cloneRow('uraian_r', $jumlahScope);

                foreach ($scopes as $index => $scope) {
                    $rowNum = $index + 1;
                    $template->setValue('aktifitas#' . $rowNum, $scope->kode_aktivitas);
                    $template->setValue('uraian#' . $rowNum, $scope->uraian);
                    $template->setValue('target#' . $rowNum, '100%');

                    $jumlahDikerjakan = $report->dailyTasks->where('scope_id', $scope->id)->count();
                    $capaianPersen = ($jumlahDikerjakan > 0) ? '100%' : '0%';

                    $template->setValue('no_r#' . $rowNum, $rowNum);
                    $template->setValue('uraian_r#' . $rowNum, $scope->uraian);
                    $template->setValue('target_r#' . $rowNum, '100%');
                    $template->setValue('jml_req#' . $rowNum, $jumlahDikerjakan);
                    $template->setValue('jml_done#' . $rowNum, $jumlahDikerjakan);
                    $template->setValue('capaian#' . $rowNum, $capaianPersen);
                }
            } else {
                // (Isi dengan block else default seperti sebelumnya)
                $template->setValue('aktifitas', '-'); $template->setValue('uraian', '-'); $template->setValue('target', '-');
                $template->setValue('no_r', '-'); $template->setValue('uraian_r', '-'); $template->setValue('target_r', '-');
                $template->setValue('jml_req', '0'); $template->setValue('jml_done', '0'); $template->setValue('capaian', '0%');
            }

            // --- PROSES TABEL KEGIATAN & LAMPIRAN FOTO ---
            // (Kode ini sama persis dengan yang sebelumnya)
            $tasks = $report->dailyTasks->sortBy('tanggal')->values();
            $jumlahTask = $tasks->count();

            if ($jumlahTask > 0) {
                $template->cloneRow('hari', $jumlahTask);
                $nomorGambar = 1;

                foreach ($tasks as $index => $task) {
                    $rowNum = $index + 1;
                    $hari = Carbon::parse($task->tanggal)->locale('id')->isoFormat('dddd');
                    $tanggal = Carbon::parse($task->tanggal)->format('d M Y');
                    $keterangan = $task->scope ? $task->scope->kode_aktivitas : '-';
                    $deskripsi = $task->deskripsi_pekerjaan;

                    if (stripos($deskripsi, 'cuti') !== false || stripos($deskripsi, 'libur') !== false) {

                        $textHari = new TextRun();
                        $textTanggal = new TextRun();
                        $textDeskripsi = new TextRun();

                        $textHari->addText($hari, ['bold' => true, 'color' => 'FF0000']); // Warna merah
                        $textTanggal->addText($tanggal, ['bold' => true, 'color' => 'FF0000']); // Warna merah
                        $textDeskripsi->addText($deskripsi, ['bold' => true, 'color' => 'FF0000']); // Warna merah

                        $template->setComplexValue('hari#' . $rowNum, $textHari);
                        $template->setComplexValue('tanggal#' . $rowNum, $textTanggal);
                        $template->setComplexValue('deskripsi#' . $rowNum, $textDeskripsi);
                    } else {
                        $template->setValue('hari#' . $rowNum, $hari);
                        $template->setValue('tanggal#' . $rowNum, $tanggal);
                        $template->setValue('deskripsi#' . $rowNum, $deskripsi);
                    }

                    $template->setValue('keterangan#' . $rowNum, $keterangan);

                    $teksDokumentasi = [];
                    foreach ($task->taskImages as $img) {
                        $teksDokumentasi[] = "Gambar " . $nomorGambar;
                        $nomorGambar++;
                    }
                    $template->setValue('dokumentasi#' . $rowNum, implode(', ', $teksDokumentasi));
                }
            } else {
                $template->setValue('hari', '-'); $template->setValue('tanggal', '-');
                $template->setValue('deskripsi', '-'); $template->setValue('keterangan', '-'); $template->setValue('dokumentasi', '-');
            }

            // PROSES LAMPIRAN FOTO
            $semuaFoto = [];
            $nomorGambarLampi = 1;
            foreach ($tasks as $task) {
                foreach ($task->taskImages as $img) {
                    $path = storage_path('app/public/' . $img->image_path);
                    if (file_exists($path)) {
                        $semuaFoto[] = ['path' => $path, 'caption' => "Gambar {$nomorGambarLampi} " . $task->deskripsi_pekerjaan];
                        $nomorGambarLampi++;
                    }
                }
            }

            $chunks = array_chunk($semuaFoto, 2);
            $jumlahBarisFoto = count($chunks);

            if ($jumlahBarisFoto > 0) {
                $template->cloneRow('caption_1', $jumlahBarisFoto);
                foreach ($chunks as $index => $chunk) {
                    $rowNum = $index + 1;
                    $template->setImageValue('foto_1#' . $rowNum, ['path' => $chunk[0]['path'], 'width' => 250, 'ratio' => true]);
                    $template->setValue('caption_1#' . $rowNum, $chunk[0]['caption']);
                    if (isset($chunk[1])) {
                        $template->setImageValue('foto_2#' . $rowNum, ['path' => $chunk[1]['path'], 'width' => 250, 'ratio' => true]);
                        $template->setValue('caption_2#' . $rowNum, $chunk[1]['caption']);
                    } else {
                        $template->setValue('foto_2#' . $rowNum, ''); $template->setValue('caption_2#' . $rowNum, '');
                    }
                }
            } else {
                $template->setValue('foto_1', ''); $template->setValue('caption_1', '-'); $template->setValue('foto_2', ''); $template->setValue('caption_2', '');
            }

            $fileName = "Laporan_Kinerja_{$namaBulan}_{$report->tahun}.docx";
            $tempPath = storage_path('app/public/' . $fileName);
            $template->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // TAMPILKAN ERROR DETAIL BUKAN 500 BLANK
            return "<div style='padding:20px; color:#991b1b; background:#fee2e2; border:1px solid #ef4444;'> Error: " . $e->getMessage() . " di baris " . $e->getLine() . "</div>";
        }
    }
}
