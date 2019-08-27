<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\RoleTrait;

class Support extends User
{
    use RoleTrait;

    public $userRole = 'support';

    public function profile()
    {
        return $this->hasOne(SupportProfile::class, 'user_id');
    }
}
