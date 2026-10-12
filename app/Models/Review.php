<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'order_item_id', 'customer_id', 'product_id', 'product_rating', 'artisan_rating',
        'packaging_rating', 'shipping_rating', 'body', 'artisan_reply', 'replied_at', 'media', 'is_published',
    ];

    protected function casts(): array
    {
        return ['media' => 'array', 'is_published' => 'boolean', 'replied_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
