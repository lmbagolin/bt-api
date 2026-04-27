<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeagueStageGroup extends Model
{
    protected $fillable = ['league_stage_id', 'letter'];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeagueStage::class, 'league_stage_id');
    }

    public function groupRegistrations(): HasMany
    {
        return $this->hasMany(LeagueStageGroupRegistration::class, 'group_id');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(LeagueStageMatch::class, 'group_id')->orderBy('match_number');
    }
}
