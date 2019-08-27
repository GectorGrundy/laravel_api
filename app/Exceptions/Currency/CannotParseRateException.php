<?php
declare(strict_types=1);

namespace App\Exceptions\Currency;

use RuntimeException;

class CannotParseRateException extends RuntimeException
{
	public function __construct()
	{
		parent::__construct();
	}
}
