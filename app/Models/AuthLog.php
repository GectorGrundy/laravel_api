<?php
declare(strict_types=1);

namespace App\Models;

class AuthLog extends AbstractEntity
{
    public $fillable = ['user_id', 'ip', 'user_agent'];
}
