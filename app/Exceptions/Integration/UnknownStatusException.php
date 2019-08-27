<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class UnknownStatusException extends RuntimeException
{
	public function __construct($status)
	{
		parent::__construct('Unknown integration status - ' . $status);
	}
}
