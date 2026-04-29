<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueStage extends Model
{
    protected $fillable = [
        'league_id',
        'data_etapa',
        'data_abertura_inscricoes',
        'valor_inscricao',
        'tipo',
        'jogadores_por_grupo',
        'vagas',
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
        'stage_status',
    ];

    protected $casts = [
        'data_etapa' => 'datetime',
        'data_abertura_inscricoes' => 'date',
        'disputa_3_lugar' => 'boolean',
    ];

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(LeagueStageRegistration::class);
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'league_stage_players')
            ->withPivot('player_status')
            ->withTimestamps();
    }

    public function groups(): HasMany
    {
        return $this->hasMany(LeagueStageGroup::class, 'league_stage_id')->orderBy('letter');
    }

    public function finalists(): HasMany
    {
        return $this->hasMany(LeagueStageFinalist::class, 'league_stage_id');
    }

    public function playoffPairs(): HasMany
    {
        return $this->hasMany(LeagueStagePlayoffPair::class, 'league_stage_id')->orderBy('pair_rank');
    }

    public function playoffMatches(): HasMany
    {
        return $this->hasMany(LeagueStagePlayoffMatch::class, 'league_stage_id');
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(LeagueStageRanking::class, 'league_stage_id')->orderBy('position');
    }

    public function stageStatus(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $dataAbertura = $attributes['data_abertura_inscricoes'] ?? null;
                $registrationsOpen = is_null($dataAbertura)
                    || $dataAbertura <= now();
                $stageStarted = $attributes['data_etapa'] <= now();
                $openStatus = ['created'];

                if (
                    $registrationsOpen &&
                    !$stageStarted &&
                    in_array($value, $openStatus)
                ) {
                    return 'registrations_open';
                }
                return $value;
            },
        );
    }
}
