<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUserPermission extends AbstractEntity
{
    protected $table = 'user_user_permission';
    protected $fillable = ['user_id', 'user_permission_id'];
}
