<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'tournament_id',
        'name',
        'gender',
        'level',
    ];

    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}
