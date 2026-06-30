<?php

namespace App\Services;

use App\Models\User;
use App\Models\Leave;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LeaveService
{
    /**
     * Mengajukan cuti, memvalidasi jatah, dan mensinkronisasikan ke laporan bulanan.
     */
    public function applyForLeave(User $user, string $dateString, string $description): Leave
    {
        return DB::transaction(function () use ($user, $dateString, $description) {
            $date = Carbon::parse($dateString);
            $year = $date->year;
            $month = $date->month;

            // 1. Cek Jatah Cuti
            $stats = $user->getLeaveStats($year);
            if ($stats['remaining'] <= 0) {
                throw new Exception("Pengajuan gagal! Jatah cuti kamu untuk tahun {$year} sudah habis.");
            }

            // 2. Cek Duplikasi Pengajuan pada Tanggal yang Sama
            $alreadyApplied = $user->leaves()->whereDate('tanggal_cuti', $dateString)->exists();
            if ($alreadyApplied) {
                throw new Exception("Kamu sudah mengajukan cuti di tanggal tersebut!");
            }

            // 3. Simpan Data Cuti
            $leave = $user->leaves()->create([
                'tanggal_cuti' => $dateString,
                'keterangan' => $description,
            ]);

            // 4. Sinkronisasi dengan Laporan Bulanan
            $this->syncLeaveWithReport($user, $leave, $month, $year);

            return $leave;
        });
    }

    /**
     * Sinkronisasikan data cuti ke laporan harian.
     */
    protected function syncLeaveWithReport(User $user, Leave $leave, int $month, int $year): void
    {
        $report = Report::where('user_id', $user->id)
                        ->where('bulan', $month)
                        ->where('tahun', $year)
                        ->first();

        if (!$report) {
            // Cari kontrak aktif pada tanggal cuti tersebut
            $activeContract = $user->contracts()
                ->whereDate('tanggal_mulai', '<=', $leave->tanggal_cuti)
                ->whereDate('tanggal_selesai', '>=', $leave->tanggal_cuti)
                ->first();

            if ($activeContract) {
                $report = Report::create([
                    'user_id' => $user->id,
                    'bulan' => $month,
                    'tahun' => $year,
                    'contract_id' => $activeContract->id
                ]);
            }
        }

        if ($report) {
            $report->dailyTasks()->create([
                'scope_id' => null,
                'tanggal' => $leave->tanggal_cuti,
                'deskripsi_pekerjaan' => 'Cuti: ' . $leave->keterangan,
            ]);
        }
    }

    /**
     * Membatalkan cuti secara aman dan menghapus placeholder daily task terkait.
     */
    public function cancelLeave(Leave $leave): void
    {
        DB::transaction(function () use ($leave) {
            $user = $leave->user;
            $date = Carbon::parse($leave->tanggal_cuti);

            // Cari dan hapus task cuti terkait
            $report = $user->reports()
                ->where('bulan', $date->month)
                ->where('tahun', $date->year)
                ->first();

            if ($report) {
                $report->dailyTasks()
                    ->whereDate('tanggal', $date)
                    ->whereNull('scope_id')
                    ->where('deskripsi_pekerjaan', 'Cuti: ' . $leave->keterangan)
                    ->delete();
            }

            $leave->delete();
        });
    }
}
