<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineCityNames extends RuntimeException
{
	public function __construct($message = "Couldn't determine city names")
	{
		parent::__construct($message);
	}
}
