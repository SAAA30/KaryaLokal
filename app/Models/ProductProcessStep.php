<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductProcessStep extends Model
{
    protected $fillable = [
        'product_id', 'step_number', 'title', 'description', 'media_path', 'duration_minutes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
