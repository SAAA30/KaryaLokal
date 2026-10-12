<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('order_item_id')->nullable()->change();
            $table->text('artisan_reply')->nullable()->after('body');
            $table->timestamp('replied_at')->nullable()->after('artisan_reply');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['artisan_reply', 'replied_at']);
        });
    }
};
