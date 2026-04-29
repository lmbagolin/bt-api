<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    public $incrementing = false;

    protected $fillable = ['id', 'name', 'state_code', 'is_capital', 'metadata'];

    protected $casts = [
        'is_capital' => 'boolean',
        'metadata' => 'array',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_code', 'code');
    }

    public function arenas(): HasMany
    {
        return $this->hasMany(Arena::class, 'city_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'city_id');
    }
}
