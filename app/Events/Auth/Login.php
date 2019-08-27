<?php
declare(strict_types=1);

namespace App\Events\Auth;

use App\Events\Event;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

class Login extends Event
{
    use SerializesModels;

    /**
     * @var User
     */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
