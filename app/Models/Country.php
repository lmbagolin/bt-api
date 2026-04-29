<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $primaryKey = 'iso3';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['iso3', 'iso2', 'name'];

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_iso3', 'iso3');
    }
}
