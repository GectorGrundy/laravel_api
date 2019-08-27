<?php
declare(strict_types=1);

namespace App\Exceptions\Request;

use RuntimeException;

class IncorrectParameterException extends RuntimeException
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
