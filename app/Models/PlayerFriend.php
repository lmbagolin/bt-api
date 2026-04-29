<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerFriend extends Model
{
    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
        'token',
        'token_expires_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'addressee_id');
    }
}
