<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'user_id', 'job_package_id', 'nama_kontrak', 'jabatan', 'tujuan', 'sasaran', 'ruang_lingkup',
        'tanggal_mulai', 'tanggal_selesai', 'spk_nomor', 'spk_tanggal',
        'spmk_nomor', 'spmk_tanggal', 'kuota_cuti'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function jobPackage() {
        return $this->belongsTo(JobPackage::class);
    }

    public function reports() {
        return $this->hasMany(Report::class);
    }
}
