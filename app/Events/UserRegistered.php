<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\User;

class UserRegistered extends Event
{
    public $user;
    public $request;

    public function __construct(User $user, array $request)
    {
        $this->user = $user;
        $this->request = $request;
    }
}
