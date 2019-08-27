<?php
declare(strict_types=1);

namespace App\Models;

class AdministratorProfile extends AbstractEntity
{
    protected $fillable = ['full_name', 'skype', 'telegram', 'user_id'];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
