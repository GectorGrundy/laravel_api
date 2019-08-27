<?php
declare(strict_types=1);

namespace App\Exceptions\Visitor;

use RuntimeException;

class DoNotExistsSid extends RuntimeException
{
	public function __construct($message = 'Incorrect user identifier')
	{
		parent::__construct($message);
	}
}
