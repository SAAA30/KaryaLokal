<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_REVISION = 'revision';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'artisan_id', 'category_id', 'name', 'slug', 'sku', 'description', 'story',
        'material', 'technique', 'origin', 'dimensions', 'weight_grams', 'price',
        'stock', 'fulfillment_type', 'production_days', 'is_customizable', 'images',
        'verification_status', 'verification_note', 'verified_at', 'craft_id', 'qr_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'images' => 'array',
            'is_customizable' => 'boolean',
            'qr_active' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function processSteps(): HasMany
    {
        return $this->hasMany(ProductProcessStep::class)->orderBy('step_number');
    }

    public function qrScans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
