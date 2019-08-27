<?php
declare(strict_types=1);

namespace App\Exceptions\Visitor;

use RuntimeException;

class IncorrectCacheDataException extends RuntimeException
{
	public function __construct($message = 'Incorrect cache data')
	{
		parent::__construct($message);
	}
}
