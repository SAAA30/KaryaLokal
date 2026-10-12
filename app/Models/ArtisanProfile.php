<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtisanProfile extends Model
{
    protected $fillable = [
        'user_id', 'bio', 'maker_story', 'village', 'city', 'province',
        'specialty', 'started_year', 'portfolio', 'verification_status',
        'verification_note', 'verified_at',
    ];

    protected function casts(): array
    {
        return ['portfolio' => 'array', 'verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
