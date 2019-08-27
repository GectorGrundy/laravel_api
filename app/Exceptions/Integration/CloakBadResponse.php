<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class CloakBadResponse extends RuntimeException
{
	public function __construct($message = 'Incorrect cache data')
	{
		parent::__construct($message);
	}
}
