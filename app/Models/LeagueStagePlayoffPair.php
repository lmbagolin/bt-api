<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStagePlayoffPair extends Model
{
    protected $fillable = [
        'league_stage_id',
        'finalist1_id',
        'finalist2_id',
        'pair_rank',
        'pts_total',
        'gp_total',
        'gc_total',
        'position_sum',
    ];

    protected $appends = ['sg_total'];

    public function getSgTotalAttribute(): int
    {
        return $this->gp_total - $this->gc_total;
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeagueStage::class, 'league_stage_id');
    }

    public function finalist1(): BelongsTo
    {
        return $this->belongsTo(LeagueStageFinalist::class, 'finalist1_id');
    }

    public function finalist2(): BelongsTo
    {
        return $this->belongsTo(LeagueStageFinalist::class, 'finalist2_id');
    }
}
