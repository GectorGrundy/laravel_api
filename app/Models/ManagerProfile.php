<?php
declare(strict_types=1);

namespace App\Models;

class ManagerProfile extends AbstractEntity
{
    protected $fillable = ['full_name', 'skype', 'telegram', 'phone'];
    protected $hidden = ['id', 'user_id', 'created_at', 'updated_at'];
}
