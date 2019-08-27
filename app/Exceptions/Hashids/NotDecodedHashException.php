<?php
declare(strict_types=1);

namespace App\Exceptions\Hashids;

use RuntimeException;

class NotDecodedHashException extends RuntimeException
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
