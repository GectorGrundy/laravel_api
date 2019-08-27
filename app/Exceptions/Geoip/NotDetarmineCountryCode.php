<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineCountryCode extends RuntimeException
{
	public function __construct($message = "Couldn't determine country code")
	{
		parent::__construct($message);
	}
}
