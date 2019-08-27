<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class IncorrectFtpConfigException extends RuntimeException
{
	public function __construct($integration_name, $integration_info)
	{
		parent::__construct("Incorrect ftp config for {$integration_name}[{$integration_info}] integration.");
	}
}
