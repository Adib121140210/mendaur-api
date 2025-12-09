<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $fillable = [
        'nama',
        'deskripsi',
        'icon',
        'syarat',
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_badges',
            'badge_id',
            'user_id'
        )->withPivot('tanggal_dapat')->withTimestamps();
    }
}
