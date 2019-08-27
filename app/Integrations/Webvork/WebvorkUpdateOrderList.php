<?php
declare(strict_types=1);

namespace App\Integrations\Webvork;

use Carbon\Carbon;
use App\Models\{
    Integration, Lead
};
use Illuminate\Console\Command;
use App\Exceptions\Integration\BadResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class WebvorkUpdateOrderList extends Command
{
    public const INTEGTATION_TITLE = 'Webvork';

    private const NEW_TYPE = 'new';
    private const WEEK_TYPE = 'week';
    private const ITEMPAGE = 50;

    protected $signature = 'webvork:update_order_list {type}';
    protected $description = 'Get orders from Webvork and update statuses';

    /**
     * @var Webvork
     */
    private $webvork;

    public function __construct()
    {
        parent::__construct();

        $this->webvork = app(Webvork::class);
    }

    public function handle()
    {
        $this->validateType();

        $integrations = (new Integration())->getActiveByTitle(self::INTEGTATION_TITLE);

        if (!$integrations->count()) {
            return;
        }

        foreach ($integrations as $integration) {

            $this->getLeadsQuery($integration)->chunk(self::ITEMPAGE, function ($leads) use ($integration) {

                $lead_external_keys = $this->getLeadsId($leads);
                $this->webvork->setToken($integration['integration_data_array']['token']);

                $orders = $this->webvork->getOrders($lead_external_keys, true);

                $this->processLeads($orders);
            });
        }
    }

    private function processLeads(iterable $orders)
    {
        foreach ($orders AS $order) {

            try {
                $lead = Lead::where('external_key', $order['guid'])->firstOrFail();
            } catch (ModelNotFoundException $e) {
                continue;
            }

            switch ($order['status']) {
                case 'confirmed':
                    $lead->approveIfNotAproved();
                    break;

                case 'rejected':
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

    private function getLeadsId(Collection $leads)
    {
        $lead_external_keys = [];
        foreach ($leads as &$lead) {
            $lead_external_keys[] = $lead['external_key'];
        }
        unset($lead);
        return $lead_external_keys;
    }

    private function validateType()
    {
        if (!\in_array($this->argument('type'), [self::NEW_TYPE, self::WEEK_TYPE])) {
            throw new \BadMethodCallException('Unknown type.');
        }
    }
}
