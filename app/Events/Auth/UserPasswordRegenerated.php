<?php
declare(strict_types=1);

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class UserPasswordRegenerated
{
    use SerializesModels;

    /**
     * @var User
     */
    public $user;
    /**
     * @var string
     */
    public $new_password;

    public function __construct(User $user, string $new_password)
    {
        $this->user = $user;
        $this->new_password = $new_password;
    }
}
