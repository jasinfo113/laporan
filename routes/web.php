<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobPackageController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Routes untuk Laporan Harian ---
    Route::resource('reports', ReportController::class);
    Route::post('/reports/{report}/tasks', [DailyTaskController::class, 'store'])->name('tasks.store');
    Route::patch('/daily-tasks/{id}', [DailyTaskController::class, 'update'])->name('tasks.update');
    Route::delete('/daily-tasks/{id}', [DailyTaskController::class, 'destroy'])->name('tasks.destroy');
    Route::delete('/task-images/{id}', [DailyTaskController::class, 'destroyImage'])->name('task-images.destroy');
    // Ekspor laporan ke Word
    Route::get('/reports/{report}/export', [ReportController::class, 'exportWord'])->name('reports.export');
    Route::resource('leaves', LeaveController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('contracts', ContractController::class);
});

Route::middleware(['auth', IsAdmin::class])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('job_packages', JobPackageController::class);
    Route::resource('approvers', ApproverController::class);
});

require __DIR__.'/auth.php';
