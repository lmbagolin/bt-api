<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageMatch extends Model
{
    protected $fillable = [
        'group_id',
        'match_number',
        'd1_player1_id',
        'd1_player2_id',
        'd2_player1_id',
        'd2_player2_id',
        'score_p',
        'score_q',
    ];

    protected $casts = [
        'score_p' => 'integer',
        'score_q' => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LeagueStageGroup::class, 'group_id');
    }

    public function d1Player1(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'd1_player1_id');
    }

    public function d1Player2(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'd1_player2_id');
    }

    public function d2Player1(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'd2_player1_id');
    }

    public function d2Player2(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'd2_player2_id');
    }
}
