<?php
declare(strict_types = 1);

namespace App\Integrations\Weblab;

use App\Models\Lead;
use App\Integrations\ReleaseJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
	SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class WeblabAddOrder implements ShouldQueue
{
	use Queueable;
	use SerializesModels;
	use InteractsWithQueue;
	use ReleaseJob;

	/**
	 * @var Weblab $weblab
	 */

	private $lead_id;

	public function __construct(int $lead_id)
	{
		$this->lead_id = $lead_id;
	}

	public function handle(): void
	{
		$weblab = new Weblab();

		$lead = Lead::with(['order', 'target_geo_rule', 'integration'])->findOrFail($this->lead_id);
		if ($lead->cannotIntegrate()) {
			return;
		}

		$this->prepareWeblabParams($lead, $weblab);

		$response = $weblab->createOrder(true);

		if ($this->isErrorResponse($response)) {
			$this->releaseJob();
			return;
		}

		$lead->setAsIntegrated($lead->generateExternalKeyById());
	}

	private function prepareWeblabParams(Lead $lead, Weblab $weblab)
	{
		$weblab->setName($lead->order['name']);
		$weblab->setPhone($lead->order['phone']);
		$weblab->setPartnerId($lead->integration['integration_data_array']['partner_id']);
		$weblab->setTransactionId($lead['hash']);
		$weblab->setProductId($lead->target_geo_rule['integration_data_array']['product_id']);
	}

	private function isErrorResponse(array $response)
	{
		return empty($response['purchase']['id']);
	}
}