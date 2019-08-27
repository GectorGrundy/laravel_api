<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineRegionNames extends RuntimeException
{
	public function __construct($message = "Couldn't determine region names")
	{
		parent::__construct($message);
	}
}
