<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('artisan_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->text('maker_story')->nullable();
            $table->string('village')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('specialty')->nullable();
            $table->unsignedSmallInteger('started_year')->nullable();
            $table->json('portfolio')->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->text('story')->nullable();
            $table->string('material')->nullable();
            $table->string('technique')->nullable();
            $table->string('origin')->nullable();
            $table->string('dimensions')->nullable();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('fulfillment_type')->default('ready_stock');
            $table->unsignedSmallInteger('production_days')->nullable();
            $table->boolean('is_customizable')->default(false);
            $table->json('images')->nullable();
            $table->string('verification_status')->default('draft');
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('craft_id')->nullable()->unique();
            $table->boolean('qr_active')->default(false);
            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamps();
            $table->index(['verification_status', 'category_id']);
            $table->index(['artisan_id', 'verification_status']);
        });

        Schema::create('product_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('step_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'step_number']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('variation')->nullable();
            $table->text('custom_request')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('gift_message')->nullable();
            $table->string('gift_theme')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_phone', 30);
            $table->text('shipping_address');
            $table->string('city');
            $table->string('province');
            $table->string('postal_code', 12);
            $table->string('shipping_method');
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('awaiting_payment');
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('recipient_gift_name')->nullable();
            $table->string('gift_sender_name')->nullable();
            $table->text('gift_message')->nullable();
            $table->string('gift_theme')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('artisan_id')->constrained('users')->restrictOnDelete();
            $table->string('product_name');
            $table->string('craft_id')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->string('variation')->nullable();
            $table->text('custom_request')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->text('gift_message')->nullable();
            $table->timestamps();
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'product_id']);
        });

        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_hash', 64)->nullable()->index();
            $table->timestamp('scanned_at')->useCurrent();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index(['product_id', 'scanned_at']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('product_rating');
            $table->unsignedTinyInteger('artisan_rating')->nullable();
            $table->unsignedTinyInteger('packaging_rating')->nullable();
            $table->unsignedTinyInteger('shipping_rating')->nullable();
            $table->text('body')->nullable();
            $table->json('media')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category');
            $table->string('subject');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->string('status')->default('open');
            $table->text('resolution')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_process_steps');
        Schema::dropIfExists('products');
        Schema::dropIfExists('artisan_profiles');
        Schema::dropIfExists('categories');
    }
};
