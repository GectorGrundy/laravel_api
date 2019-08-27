<?php
declare(strict_types=1);

namespace App\Exceptions\Currency;

use RuntimeException;

class IncorrectCodeException extends RuntimeException
{
	public function __construct($currency_code)
	{
		parent::__construct('Incorrenct currency code - ' . $currency_code);
	}
}
