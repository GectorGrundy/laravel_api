<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\RoleTrait;

class Manager extends User
{
    use RoleTrait;

    public $userRole = 'manager';

    public function profile()
    {
        return $this->hasOne(ManagerProfile::class, 'user_id');
    }
}
