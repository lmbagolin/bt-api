<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arena extends Model
{
    protected $fillable = [
        'name',
        'city',
        'owner_id',
        'logo_id',
    ];

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
