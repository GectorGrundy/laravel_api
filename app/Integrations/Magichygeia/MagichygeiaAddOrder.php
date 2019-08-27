<?php
declare(strict_types=1);

namespace App\Integrations\Magichygeia;

use App\Integrations\Webvork\Webvork;
use App\Models\Lead;
use App\Integrations\ReleaseJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class MagichygeiaAddOrder implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;
    use ReleaseJob;

    /**
     * @var Webvork $webvork
     */

    private $lead_id;

    public function __construct(int $lead_id)
    {
        $this->lead_id = $lead_id;
    }

    public function handle(): void
    {
        $magichygeia = new Magichygeia();
        $lead = Lead::with(['order', 'target_geo_rule', 'integration'])->findOrFail($this->lead_id);

        if ($lead->cannotIntegrate()) {
            return;
        }

        $this->initSetters($lead, $magichygeia);

        $response = $magichygeia->createOrder(true);

        if ($this->isErrorResponse($response)) {
            $this->releaseJob();
            return;
        }

        $lead->setAsIntegrated($response['guid']);
    }

    private function initSetters(Lead $lead, Magichygeia $magichygeia)
    {
        $custom = (array) json_decode($lead->order['custom']);

        $magichygeia->setSelfKey($lead->integration['integration_data_array']['self_key']);
        $magichygeia->setCustomerName($lead->order['name']);
        $magichygeia->setCustomerPhone($lead->order['phone']);

        $magichygeia->setProductTitle($custom['title'] ?? '');
        $magichygeia->setProductCombo($custom['combo'] ?? '');
        $magichygeia->setProductCount($custom['count'] ?? '');
        $magichygeia->setProductNumber($custom['pn'] ?? '');
        $magichygeia->setProductSize($custom['size'] ?? '');

        $magichygeia->setCustomerAddress($custom['address'] ?? '');
        $magichygeia->setCustomerCity($custom['city'] ?? '');
        $magichygeia->setCustomerMessage($custom['message'] ?? '');

        $magichygeia->setPaymentWay($custom['pay_way'] ?? '');
        $magichygeia->setPaymentAmount($custom['money'] ?? '');
        $magichygeia->setProvince($custom['province'] ?? '');
        $magichygeia->setMarkRegion($custom['area'] ?? '');
    }

    private function isErrorResponse(array $response)
    {
        return empty($response['guid']);
    }
}
