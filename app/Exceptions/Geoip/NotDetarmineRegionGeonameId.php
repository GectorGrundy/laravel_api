<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineRegionGeonameId extends RuntimeException
{
	public function __construct($message = "Couldn't determine region geoname id")
	{
		parent::__construct($message);
	}
}
