<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    protected $fillable = ['product_id', 'user_id', 'visitor_hash', 'scanned_at', 'user_agent'];

    protected function casts(): array
    {
        return ['scanned_at' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
