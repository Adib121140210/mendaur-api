<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $table = 'personal_access_tokens';
    protected $primaryKey = 'personal_access_token_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Backward compatibility: access 'id' as alias for 'personal_access_token_id'
     */
    public function getIdAttribute()
    {
        return $this->personal_access_token_id;
    }
}
