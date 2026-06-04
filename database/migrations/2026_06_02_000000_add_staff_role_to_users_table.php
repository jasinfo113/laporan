<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'pegawai', 'staff') NOT NULL DEFAULT 'pegawai'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'staff')->update(['role' => 'pegawai']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'pegawai') NOT NULL DEFAULT 'pegawai'");
        }
    }
};
