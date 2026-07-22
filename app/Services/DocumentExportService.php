<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Element\TextRun;
use Illuminate\Support\Facades\File;
use Exception;

class DocumentExportService
{
    protected ?string $tempDir = null;

    /**
     * Mengekspor laporan bulanan ke file Word.
     * Mengembalikan absolute path file Word yang dihasilkan.
     */
    public function exportReportToWord(Report $report): string
    {
        Settings::setOutputEscapingEnabled(true);
        $this->tempDir = storage_path('app/report-export-images/' . uniqid('report_', true));
        File::ensureDirectoryExists($this->tempDir);

        try {
            // Load semua relasi
            $report->load(['user', 'dailyTasks.scope', 'dailyTasks.taskImages']);

            // Ambil kontrak yang aktif pada periode laporan tersebut secara dinamis
            $contract = $report->user->getActiveContractForPeriod($report->bulan, $report->tahun);

            if (!$contract) {
                $contract = $report->contract;
            }

            if (!$contract) {
                throw new Exception("Data kontrak tidak ditemukan pada laporan ini.");
            }

            $contract->loadMissing('jobPackage.approver');

            // Panggil file template
            $templatePath = resource_path('templates/template_laporan.docx');
            if (!file_exists($templatePath)) {
                throw new Exception("File template_laporan.docx tidak ditemukan di folder templates!");
            }

            $template = new TemplateProcessor($templatePath);

            // Ganti Variabel Header / Biodata
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

            $template->setValue('spk_nomor', $contract->spk_nomor ?? '-');
            $template->setValue('spk_tanggal', $contract->spk_tanggal ? Carbon::parse($contract->spk_tanggal)->locale('id')->translatedFormat('d F Y') : '-');
            $template->setValue('spmk_nomor', $contract->spmk_nomor ?? '-');
            $template->setValue('spmk_tanggal', $contract->spmk_tanggal ? Carbon::parse($contract->spmk_tanggal)->locale('id')->translatedFormat('d F Y') : '-');
            $template->setValue('jabatan', $contract->jabatan);
            $template->setValue('nama_kontrak', $contract->nama_kontrak);

            // Ganti data Pejabat Penandatangan
            $approver = $contract->jobPackage ? $contract->jobPackage->approver : null;
            $template->setValue('nama_pejabat', $approver ? $approver->nama : 'Belum Diset');
            $template->setValue('nip_pejabat', $approver ? $approver->nip : '-');
            $template->setValue('jabatan_pejabat', $approver ? $approver->jabatan : '-');

            // Ganti Blok List (Tujuan, Sasaran, Ruang Lingkup)
            $this->parseAndCloneBlocks($template, $contract);

            // Proses Tabel Target & Realisasi
            $this->processScopesTable($template, $report, $contract);

            // Proses Tabel Kegiatan & Lampiran Foto
            $this->processTasksTable($template, $report);

            // Proses Lampiran Foto
            $this->processTaskImages($template, $report);

            // Simpan ke file temp word
            $bulanFormat = sprintf('%02d', $report->bulan);
            $fileName = "{$bulanFormat} Laporan Bulan {$namaBulan} {$report->tahun}.docx";
            $outputPath = storage_path('app/public/' . $fileName);
            $template->saveAs($outputPath);

            return $outputPath;

        } finally {
            // Memastikan cleanup folder temp gambar berjalan walaupun ada exception
            if ($this->tempDir && File::isDirectory($this->tempDir)) {
                File::deleteDirectory($this->tempDir);
            }
        }
    }

    /**
     * Mem-parsing teks multi-baris dan men-clone block untuk auto-numbering.
     */
    protected function parseAndCloneBlocks(TemplateProcessor $template, $contract): void
    {
        $siapinListWord = function($teks, $variabelTeks) {
            $baris = explode("\n", $teks ?? '-');
            $hasil = [];

            foreach ($baris as $b) {
                $bersih = trim($b);
                if (!empty($bersih)) {
                    // Hapus angka "1. ", "2. " atau strip "- " dari input
                    $bersih = preg_replace('/^(\d+\.|\-)\s*/', '', $bersih);
                    $hasil[] = [$variabelTeks => $bersih];
                }
            }

            if (empty($hasil)) {
                $hasil[] = [$variabelTeks => '-'];
            }

            return $hasil;
        };

        $template->cloneBlock('block_tujuan', 0, true, false, $siapinListWord($contract->tujuan, 'teks_tujuan'));
        $template->cloneBlock('block_sasaran', 0, true, false, $siapinListWord($contract->sasaran, 'teks_sasaran'));
        $template->cloneBlock('block_ruang_lingkup', 0, true, false, $siapinListWord($contract->ruang_lingkup, 'teks_ruang_lingkup'));
    }

    /**
     * Mengisi tabel Target & Realisasi berdasarkan scope pekerjaan.
     */
    protected function processScopesTable(TemplateProcessor $template, Report $report, $contract): void
    {
        $scopes = $contract && $contract->jobPackage ? $contract->jobPackage->scopes : collect();
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
            $template->setValue('aktifitas', '-'); $template->setValue('uraian', '-'); $template->setValue('target', '-');
            $template->setValue('no_r', '-'); $template->setValue('uraian_r', '-'); $template->setValue('target_r', '-');
            $template->setValue('jml_req', '0'); $template->setValue('jml_done', '0'); $template->setValue('capaian', '0%');
        }
    }

    /**
     * Mengisi tabel kegiatan harian (Daily Tasks).
     */
    protected function processTasksTable(TemplateProcessor $template, Report $report): void
    {
        $tasks = $report->dailyTasks->sortBy('tanggal')->values();
        $jumlahTask = $tasks->count();

        if ($jumlahTask > 0) {
            $template->cloneRow('hari', $jumlahTask);
            $nomorGambar = 1;

            foreach ($tasks as $index => $task) {
                $rowNum = $index + 1;
                $hari = Carbon::parse($task->tanggal)->locale('id')->isoFormat('dddd');
                $tanggal = Carbon::parse($task->tanggal)->locale('id')->translatedFormat('d M Y');
                $keterangan = $task->scope ? $task->scope->kode_aktivitas : '-';
                $deskripsi = $task->deskripsi_pekerjaan;

                if (stripos($deskripsi, 'cuti') !== false || stripos($deskripsi, 'libur') !== false) {
                    $textHari = new TextRun();
                    $textTanggal = new TextRun();
                    $textDeskripsi = new TextRun();

                    $textHari->addText($hari, ['bold' => true, 'color' => 'FF0000']);
                    $textTanggal->addText($tanggal, ['bold' => true, 'color' => 'FF0000']);
                    $textDeskripsi->addText($deskripsi, ['bold' => true, 'color' => 'FF0000']);

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
    }

    /**
     * Memproses gambar bukti kegiatan harian secara efisien & aman.
     */
    protected function processTaskImages(TemplateProcessor $template, Report $report): void
    {
        $semuaFoto = [];
        $nomorGambarLampi = 1;
        $tasks = $report->dailyTasks->sortBy('tanggal')->values();

        foreach ($tasks as $task) {
            foreach ($task->taskImages as $img) {
                $path = storage_path('app/public/' . $img->image_path);
                if (file_exists($path)) {
                    $semuaFoto[] = [
                        'path' => $this->resizeImageForWord($path),
                        'caption' => "Gambar {$nomorGambarLampi}: " . $task->deskripsi_pekerjaan,
                    ];
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
    }

    /**
     * Menyiapkan gambar dengan normalisasi rotasi & kompresi (menghindari memory overflow).
     */
    protected function resizeImageForWord(string $path): string
    {
        if (!extension_loaded('gd')) {
            return $path;
        }

        $imageInfo = @getimagesize($path);
        if (!$imageInfo || empty($imageInfo['mime'])) {
            return $path;
        }

        [$width, $height] = $imageInfo;
        if ($width <= 0 || $height <= 0) {
            return $path;
        }

        // Buat file temporary jpeg baru
        $targetPath = $this->tempDir . DIRECTORY_SEPARATOR . uniqid('img_', true) . '.jpg';

        $source = match ($imageInfo['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (!$source) {
            return $path;
        }

        // Normalisasi rotasi JPEG EXIF
        if ($imageInfo['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = $exif['Orientation'] ?? null;
            $rotated = match ($orientation) {
                3 => imagerotate($source, 180, 0),
                6 => imagerotate($source, -90, 0),
                8 => imagerotate($source, 90, 0),
                default => false,
            };

            if ($rotated !== false) {
                imagedestroy($source);
                $source = $rotated;
            }
        }

        // Tulis kembali dengan kompresi 75% jpeg untuk performa dokumen
        $saved = imagejpeg($source, $targetPath, 75);
        imagedestroy($source);

        return $saved ? $targetPath : $path;
    }
}
