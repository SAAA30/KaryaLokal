<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
        });

        DB::table('users')
            ->whereIn('role', ['surveyor', 'evaluator', 'karyawan', 'penanggung_jawab'])
            ->update(['role' => 'customer']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'artisan')->update(['role' => 'karyawan']);
        DB::table('users')->where('role', 'curator')->update(['role' => 'evaluator']);
        DB::table('users')
            ->whereIn('role', ['customer', 'customer_service'])
            ->update(['role' => 'surveyor']);
    }
};
