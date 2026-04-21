<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'category_id',
        'player_id',
        'team_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
