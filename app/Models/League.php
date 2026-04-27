<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $fillable = [
        'arena_id',
        'nome',
        'data_inicio',
        'data_prevista_termino',
        'numero_etapas',
        'descricao',
        'premiacao',
        'nivel',
        'genero',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_prevista_termino' => 'date',
    ];

    public function arena(): BelongsTo
    {
        return $this->belongsTo(Arena::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(LeagueStage::class);
    }
}
