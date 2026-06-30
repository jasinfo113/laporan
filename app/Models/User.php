<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password',
        'nik', 'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi: Satu User bisa memiliki banyak Laporan (Report)
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function jobPackage() {
        return $this->belongsTo(JobPackage::class);
    }

    /**
     * Relasi: Satu User bisa memiliki banyak Kontrak (Contract)
     */
    public function contracts() {
        return $this->hasMany(Contract::class);
    }

    /**
     * Relasi: Satu User bisa memiliki banyak Cuti (Leave)
     */
    public function leaves() {
        return $this->hasMany(Leave::class);
    }

    /**
     *  FUNGSI SAKTI: Ambil Kontrak yang sedang Aktif (berdasarkan tanggal hari ini)
     */
    public function getActiveContractAttribute() {
        return $this->contracts()
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now())
            ->latest()
            ->first();
    }

    /**
     * Ambil kontrak aktif untuk periode bulan dan tahun tertentu.
     */
    public function getActiveContractForPeriod(int $month, int $year): ?Contract
    {
        $targetDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();

        return $this->contracts()
            ->whereDate('tanggal_mulai', '<=', $targetDate->endOfMonth())
            ->whereDate('tanggal_selesai', '>=', $targetDate->startOfMonth())
            ->latest()
            ->first();
    }

    /**
     * Hitung sisa dan total jatah cuti untuk tahun tertentu.
     */
    public function getLeaveStats(int $year): array
    {
        $totalQuota = (int) $this->contracts()
            ->whereYear('tanggal_mulai', $year)
            ->sum('kuota_cuti');

        $usedLeave = $this->leaves()
            ->whereYear('tanggal_cuti', $year)
            ->count();

        return [
            'total' => $totalQuota,
            'used' => $usedLeave,
            'remaining' => max(0, $totalQuota - $usedLeave)
        ];
    }
}
