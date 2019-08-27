<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineCityGeonameId extends RuntimeException
{
	public function __construct($message = "Couldn't determine city geoname id")
	{
		parent::__construct($message);
	}
}
