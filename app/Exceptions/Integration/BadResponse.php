<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class BadResponse extends RuntimeException
{
    /**
     * @param string $integration_name
     * @param array $response
     */
	public function __construct(string $integration_name, $response)
	{
		parent::__construct("Bad response from [{$integration_name}]. Response: " . json_encode($response));
	}
}
