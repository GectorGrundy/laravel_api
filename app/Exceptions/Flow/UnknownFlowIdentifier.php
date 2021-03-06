<?php
declare(strict_types=1);

namespace App\Exceptions\Flow;

use RuntimeException;

class UnknownFlowIdentifier extends RuntimeException
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
