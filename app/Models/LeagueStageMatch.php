<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeagueStageMatch extends Model
{
    protected $fillable = [
        'group_id',
        'match_number',
        'p1_registration_id',
        'p2_registration_id',
        'q1_registration_id',
        'q2_registration_id',
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

    public function p1(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'p1_registration_id');
    }

    public function p2(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'p2_registration_id');
    }

    public function q1(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'q1_registration_id');
    }

    public function q2(): BelongsTo
    {
        return $this->belongsTo(LeagueStageRegistration::class, 'q2_registration_id');
    }
}
