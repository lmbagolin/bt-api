<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = [
        'path',
        'disk',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return Storage::disk($this->disk)->temporaryUrl($this->path, now()->addMinutes(60));
    }
}
