<?php
declare(strict_types=1);

namespace App\Events\User;

use App\Events\Event;
use App\Models\User;

class UserCreated extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
