<?php
declare(strict_types=1);

namespace App\Exceptions\Landing;

use RuntimeException;

class CouldntDetectSite extends RuntimeException
{
	public function __construct($message = '')
	{
		parent::__construct($message);
	}
}
