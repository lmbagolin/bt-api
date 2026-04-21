<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'name',
        'nickname',
        'gender',
        'level',
        'city',
        'whatsapp',
        'instagram',
        'user_id',
        'image_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function image()
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function arenas()
    {
        return $this->belongsToMany(Arena::class, 'arenas_has_players');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
