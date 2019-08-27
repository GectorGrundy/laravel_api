<?php
declare(strict_types=1);

namespace App\Exceptions\Visitor;

use RuntimeException;

class TooManyOrdersException extends RuntimeException
{
	public function __construct($message = 'Too many orders')
	{
		parent::__construct($message);
	}
}
