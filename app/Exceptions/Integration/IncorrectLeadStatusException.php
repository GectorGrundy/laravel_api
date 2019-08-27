<?php
declare(strict_types=1);

namespace App\Exceptions\Integration;

use RuntimeException;

class IncorrectLeadStatusException extends RuntimeException
{
	public function __construct($lead_id, $status)
	{
		parent::__construct("Пришел статус {{$status}} для лида {{$lead_id}}");
	}
}
