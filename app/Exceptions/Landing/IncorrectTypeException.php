<?php
declare(strict_types=1);

namespace App\Exceptions\Landing;

use RuntimeException;

class IncorrectTypeException extends RuntimeException
{
	public function __construct($message = 'Unknown type')
	{
		parent::__construct($message);
	}
}
