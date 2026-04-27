<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStagePlayoffMatch extends Model
{
    protected $fillable = [
        'league_stage_id',
        'round_name',
        'match_number',
        'pair1_id',
        'pair2_id',
        'is_bye',
        'score_p',
        'score_q',
        'winner_pair_id',
    ];

    protected $casts = [
        'is_bye'  => 'boolean',
        'score_p' => 'integer',
        'score_q' => 'integer',
    ];

    public function pair1(): BelongsTo
    {
        return $this->belongsTo(LeagueStagePlayoffPair::class, 'pair1_id');
    }

    public function pair2(): BelongsTo
    {
        return $this->belongsTo(LeagueStagePlayoffPair::class, 'pair2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(LeagueStagePlayoffPair::class, 'winner_pair_id');
    }
}
