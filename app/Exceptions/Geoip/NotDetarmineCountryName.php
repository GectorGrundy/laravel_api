<?php
declare(strict_types=1);

namespace App\Exceptions\Geoip;

use RuntimeException;

class NotDetarmineCountryName extends RuntimeException
{
	public function __construct($message = "Couldn't determine country name")
	{
		parent::__construct($message);
	}
}
