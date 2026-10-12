<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereNull('tracking_number')
            ->orderBy('id')
            ->get(['id', 'order_number'])
            ->each(function (object $order): void {
                $reference = 'KL-RESI-' . strtoupper(preg_replace('/[^A-Z0-9]/', '', $order->order_number));
                DB::table('orders')->where('id', $order->id)->update(['tracking_number' => $reference]);
            });
    }

    public function down(): void
    {
        // Generated references are retained to avoid clearing real carrier numbers entered later.
    }
};