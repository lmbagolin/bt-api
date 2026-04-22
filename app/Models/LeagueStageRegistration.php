<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageRegistration extends Model
{
    protected $fillable = [
        'league_stage_id',
        'player_id',
        'status',
        'valor_pago',
        'posicao_grupo',
        'observacoes',
    ];

    protected $casts = [
        'valor_pago' => 'decimal:2',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeagueStage::class, 'league_stage_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
