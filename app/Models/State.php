<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class State extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['code', 'name', 'country_iso3'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_iso3', 'iso3');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'state_code', 'code');
    }
}
