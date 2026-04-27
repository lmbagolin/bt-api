<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageGroupRegistration extends Model
{
    protected $fillable = ['group_id', 'registration_id', 'color'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(LeagueStageGroup::class, 'group_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'registration_id');
    }
}
