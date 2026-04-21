<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = [
        'arena_id',
        'name',
        'type',
        'status',
    ];

    public function arena()
    {
        return $this->belongsTo(Arena::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}
