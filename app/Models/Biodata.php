<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biodata extends Model
{
    use HasFactory;

    protected $table = 'biodata';

    protected $fillable = [
        'nama',
        'nim',
        'deskripsi',
        'foto',
    ];

    protected $appends = [
        'foto_url',
    ];

    /**
     * Accessor untuk foto_url
     */
    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto) {
            return asset('uploads/' . $this->foto);
        }

        return null;
    }
}
