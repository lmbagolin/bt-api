<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'nickname',
        'gender',
        'level',
        'city_id',
        'nationality',
        'whatsapp',
        'instagram',
        'user_id',
        'image_id',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality', 'iso3');
    }

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

    public function leagueStages(): BelongsToMany
    {
        return $this->belongsToMany(LeagueStage::class, 'league_stage_players')
            ->withPivot('player_status')
            ->withTimestamps();
    }
}
