<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStage extends Model
{
    protected $fillable = [
        'league_id',
        'data_etapa',
        'valor_inscricao',
        'tipo',
        'jogadores_por_grupo',
        'classificam_total',
        'disputa_3_lugar',
        'pontuacao_1',
        'pontuacao_2',
        'pontuacao_3',
        'pontuacao_4',
        'pontuacao_classificados',
        'pontuacao_fase_grupo',
        'pontuacao_extra_1_grupo',
        'sorteio_playoffs',
        'confrontos_playoffs',
        'sorteio_grupos',
    ];

    protected $casts = [
        'data_etapa' => 'date',
        'disputa_3_lugar' => 'boolean',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}
