<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class IncorrectExternalKey extends RuntimeException
{
	public function __construct($integration_name, $lead_id)
	{
		parent::__construct("There is invalid external_key for lead #{{$lead_id}} in integration {{$integration_name}}");
	}
}
