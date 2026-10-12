<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    private array $legacyTables = [
        'pengecekan_beda_luas',
        'permintaan_ttd',
        'dokumen_berkas',
        'sertifikasi',
        'sanggahan',
        'pengumuman',
        'data_yuridis',
        'kendala_lapangan',
        'persetujuan_batas',
        'dokumentasi_fisik',
        'data_fisik',
        'penugasan',
        'dokumen_persyaratan',
        'permohonan_ptsl',
        'target_ptsl',
        'kelurahan',
        'kecamatan',
        'riwayat_status',
        'log_aktivitas',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        try {
            foreach ($this->legacyTables as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->delete();
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Legacy PTSL data cannot be restored by rollback. Restore it from a backup.');
    }
};
