<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class ToManyAttempts extends RuntimeException
{
    public function __construct(int $lead_id)
    {
        parent::__construct("Too many attempts to send lead {{$lead_id}} to integration [" . __CLASS__ . "]");
    }
}
