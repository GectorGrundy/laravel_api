<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class UnknownHostException extends RuntimeException
{
	public function __construct($host)
	{
		parent::__construct('Unknown integration host - ' . $host);
	}
}
