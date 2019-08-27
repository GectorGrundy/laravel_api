<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class FailAuth extends RuntimeException
{
	public function __construct(string $integration_name, $response)
	{
		parent::__construct("{$integration_name} failes auth. Response: " . serialize($response));
	}
}
