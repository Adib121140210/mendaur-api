<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'badges';
    protected $primaryKey = 'badge_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'nama',
        'deskripsi',
        'icon',
        'syarat_poin',
        'syarat_setor',
        'reward_poin',
        'tipe',
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

    /**
     * Accessor for legacy 'id' usage
     * Maps to the primary key 'badge_id'
     */
    public function getIdAttribute()
    {
        return $this->badge_id;
    }
}
