<?php
declare(strict_types=1);

namespace App\Exceptions\Lead;

use RuntimeException;

class IncorrectStatusException extends RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
