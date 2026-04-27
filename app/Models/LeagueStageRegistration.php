<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LeagueStageRegistration extends Model
{
    protected $fillable = [
        'league_stage_id',
        'player_id',
        'partner_player_id',
        'partner_name',
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

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'partner_player_id');
    }

    public function groupRegistration(): HasOne
    {
        return $this->hasOne(LeagueStageGroupRegistration::class, 'registration_id');
    }
}
