<?php
declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

class IncorrectEntityType extends RuntimeException
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
