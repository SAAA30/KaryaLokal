<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'artisan_id', 'product_name', 'craft_id', 'quantity',
        'unit_price', 'variation', 'custom_request', 'recipient_name', 'sender_name', 'gift_message',
    ];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
