<?php
declare(strict_types=1);

namespace App\Exceptions\Custom;

use RuntimeException;

class IncorrectUaException extends RuntimeException
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
