<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisan_customer_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('artisan_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'artisan_id', 'product_id']);
            $table->index(['artisan_id', 'last_message_at']);
        });

        Schema::create('artisan_customer_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('artisan_customer_chats')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['chat_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_customer_chat_messages');
        Schema::dropIfExists('artisan_customer_chats');
    }
};