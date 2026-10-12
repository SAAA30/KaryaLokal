<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ArtisanCustomerChat extends Model
{
    protected $fillable = ['customer_id', 'artisan_id', 'product_id', 'last_message_at'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ArtisanCustomerChatMessage::class, 'chat_id')->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(ArtisanCustomerChatMessage::class, 'chat_id')->latestOfMany();
    }
}