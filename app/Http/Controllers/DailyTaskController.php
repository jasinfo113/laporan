<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\Report;
use App\Models\TaskImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DailyTaskController extends Controller
{
    public function store(Request $request, Report $report)
    {
        if ($report->user_id !== Auth::id()) {
            abort(403);
        }

        $startDate = sprintf('%04d-%02d-01', $report->tahun, $report->bulan);
        $endDate = sprintf(
            '%04d-%02d-%02d',
            $report->tahun,
            $report->bulan,
            cal_days_in_month(CAL_GREGORIAN, $report->bulan, $report->tahun)
        );

        // Validasi input
        $request->validate([
            'tanggal' => "required|date|after_or_equal:{$startDate}|before_or_equal:{$endDate}",
            'scope_id' => 'nullable|exists:scopes,id', // Bisa kosong kalau cuti/libur
            'deskripsi_pekerjaan' => 'required|string',
            'task_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120' // Validasi file gambar
        ]);

        // Simpan kegiatan harian
        $task = $report->dailyTasks()->create([
            'tanggal' => $request->tanggal,
            'scope_id' => $request->scope_id,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
        ]);

        // Proses upload banyak foto sekaligus
        if ($request->hasFile('task_images')) {
            foreach ($request->file('task_images') as $foto) {
                // Simpan ke folder storage/app/public/tasks
                $path = $foto->store('tasks', 'public');

                // Simpan path ke database
                $task->taskImages()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return back()->with('success', 'Kegiatan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $task = DailyTask::with('report')->findOrFail($id);

        if ($task->report->user_id !== Auth::id()) {
            abort(403);
        }

        $report = $task->report;
        $startDate = sprintf('%04d-%02d-01', $report->tahun, $report->bulan);
        $endDate = sprintf(
            '%04d-%02d-%02d',
            $report->tahun,
            $report->bulan,
            cal_days_in_month(CAL_GREGORIAN, $report->bulan, $report->tahun)
        );

        $request->validate([
            'tanggal' => "required|date|after_or_equal:{$startDate}|before_or_equal:{$endDate}",
            'scope_id' => 'nullable|exists:scopes,id',
            'deskripsi_pekerjaan' => 'required|string',
            'task_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $task->update([
            'tanggal' => $request->tanggal,
            'scope_id' => $request->scope_id,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
        ]);

        if ($request->hasFile('task_images')) {
            foreach ($request->file('task_images') as $foto) {
                $path = $foto->store('tasks', 'public');

                $task->taskImages()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return back()->with('success', 'Kegiatan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        // Panggil task beserta relasi foto dan laporannya
        $task = \App\Models\DailyTask::with(['taskImages', 'report'])->findOrFail($id);

        // Validasi keamanan: Pastikan hanya yang punya laporan yang bisa hapus
        if ($task->report->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'Ente kadang-kadang! Ini bukan laporan lu bray.');
        }

        // Hapus fisik foto dari server (Looping semua foto milik task ini)
        foreach ($task->taskImages as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        // Hapus data foto dari database
        $task->taskImages()->delete();

        // Hapus data kegiatannya
        $task->delete();

        return back()->with('success', 'Kegiatan dan foto bukti berhasil dihapus!');
    }

    public function destroyImage($id)
    {
        $image = TaskImage::with('dailyTask.report')->findOrFail($id);

        if ($image->dailyTask->report->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Foto bukti berhasil dihapus!');
    }
}
