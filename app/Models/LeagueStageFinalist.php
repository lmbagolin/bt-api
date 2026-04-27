<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageFinalist extends Model
{
    protected $fillable = [
        'league_stage_id',
        'group_id',
        'registration_id',
        'group_position',
        'pts',
        'gp',
        'gc',
    ];

    protected $appends = ['saldo_games'];

    public function getSaldoGamesAttribute(): int
    {
        return $this->gp - $this->gc;
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeagueStage::class, 'league_stage_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LeagueStageGroup::class, 'group_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'registration_id');
    }
}
