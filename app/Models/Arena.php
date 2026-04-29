<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Arena extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'owner_id',
        'logo_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    public function leagues()
    {
        return $this->hasMany(League::class);
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'arenas_has_players');
    }

    public function logo()
    {
        return $this->belongsTo(File::class, 'logo_id');
    }
}
