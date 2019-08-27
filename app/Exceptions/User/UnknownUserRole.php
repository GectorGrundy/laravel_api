<?php
declare(strict_types=1);

namespace App\Exceptions\User;

use RuntimeException;

class UnknownUserRole extends RuntimeException
{
    public function __construct(string $role, string $message = '')
    {
        parent::__construct("Unknown role {$role}{$message}");
    }
}
