<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use App\Models\Report;
use Illuminate\Http\Request;

class DailyTaskController extends Controller
{
    public function store(Request $request, Report $report)
    {
        // Validasi input
        $request->validate([
            'tanggal' => 'required|date',
            'scope_id' => 'nullable|exists:scopes,id', // Bisa kosong kalau cuti/libur
            'deskripsi_pekerjaan' => 'required|string',
            'fotos.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' // Validasi file gambar
        ]);

        // Simpan kegiatan harian
        $task = $report->dailyTasks()->create([
            'tanggal' => $request->tanggal,
            'scope_id' => $request->scope_id,
            'deskripsi_pekerjaan' => $request->deskripsi_pekerjaan,
        ]);

        // Proses upload banyak foto sekaligus
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
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
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
        }

        // Hapus data foto dari database
        $task->taskImages()->delete();

        // Hapus data kegiatannya
        $task->delete();

        return back()->with('success', 'Kegiatan dan foto bukti berhasil dihapus!');
    }
}
