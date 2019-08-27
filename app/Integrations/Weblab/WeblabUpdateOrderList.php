<?php
declare(strict_types = 1);

namespace App\Integrations\Weblab;

use Carbon\Carbon;
use App\Models\{
	Integration, Lead
};
use Illuminate\Console\Command;
use App\Exceptions\Integration\BadResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class WeblabUpdateOrderList extends Command
{
	public const INTEGTATION_TITLE = 'Weblab';

	private const NEW_TYPE = 'new';
	private const WEEK_TYPE = 'week';
	private const ITEMPAGE = 50;

	protected $signature = 'weblab:update_order_list {type}';
	protected $description = 'Get orders from Weblab and update statuses';

	private $integration;
	private $lead;
	private $weblab;

	public function __construct(Integration $integration, Lead $lead)
	{
		parent::__construct();

		$this->weblab = app(Weblab::class);
		$this->integration = $integration;
		$this->lead = $lead;
	}

	public function handle()
	{
		$this->validateType();

		$integrations = $this->integration->getActiveByTitle(self::INTEGTATION_TITLE);
		if (!$integrations->count()) {
			return;
		}

		foreach ($integrations as $integration) {

			$this->getLeadsQuery($integration)->chunk(self::ITEMPAGE, function ($leads) use ($integration) {

				$lead_ids = $this->getLeadsId($leads);

				$this->weblab->setPartnerId($integration['integration_data_array']['partner_id']);
				$orders = $this->weblab->checkOrderStatuses($lead_ids);

				$this->processLeads($orders);
			});
		}
	}

	private function processLeads(iterable $orders)
	{
		foreach ($orders AS $order) {

			if (!$order['fetch_data_success'] || $this->skipStatus($order['status'])) {
				continue;
			}

			try {
				$lead = $this->lead->where('hash', $order['transaction_id'])->firstOrFail();
			} catch (ModelNotFoundException $e) {
				continue;
			}

			switch ($order['status']) {
				case 'confirmed':
					$lead->approveIfNotAproved();
					break;
				case 'cancelled':
					$lead->cancelIfNotCancelled();
					break;
				case 'trash':
					$lead->trashIfNotTrashed();
					break;
			}
		}
	}

	private function getLeadsQuery($integration): Builder
	{
		$query = Lead::whereIntegration($integration)->whereIntegrated();

		switch ($this->argument('type')) {
			case self::NEW_TYPE:
				return $query->where('status', [Lead::NEW]);


			case self::WEEK_TYPE:
				$week_ago = Carbon::now()->subDays(7)->toDateTimeString();
				return $query->createdFrom($week_ago);
		}
	}

	private function skipStatus(string $status)
	{
		return $status === 'hold';
	}

	private function getLeadsId(Collection $leads)
	{
		$lead_ids = [];
		foreach ($leads as &$lead) {
			$lead_ids[] = $lead['hash'];
		}
		unset($lead);
		return $lead_ids;
	}

	private function validateType()
	{
		if (!\in_array($this->argument('type'), [self::NEW_TYPE, self::WEEK_TYPE])) {
			throw new \BadMethodCallException('Unknown type.');
		}
	}
}