<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageRanking extends Model
{
    protected $fillable = [
        'league_stage_id',
        'registration_id',
        'position',
        'points',
        'wins',
        'matches_played',
        'games_pro',
        'games_against',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeagueStage::class, 'league_stage_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'registration_id');
    }
}
