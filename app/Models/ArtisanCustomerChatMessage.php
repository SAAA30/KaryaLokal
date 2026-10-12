<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanCustomerChatMessage extends Model
{
    protected $fillable = ['chat_id', 'sender_id', 'body', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(ArtisanCustomerChat::class, 'chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}