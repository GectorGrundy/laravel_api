<?php
declare(strict_types=1);

namespace App\Integrations\Webvork;

use App\Integrations\Webvork\Webvork;
use App\Models\Lead;
use App\Integrations\ReleaseJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class WebvorkAddOrder implements ShouldQueue
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
        $webvork = new Webvork();
        $lead = Lead::with(['order', 'target_geo_rule', 'integration'])->findOrFail($this->lead_id);

        if ($lead->cannotIntegrate()) {
            return;
        }

        $this->initSetters($lead, $webvork);

        $response = $webvork->createOrder(true);

        if ($this->isErrorResponse($response)) {
            $this->releaseJob();
            return;
        }

        $lead->setAsIntegrated($response['guid']);
    }

    private function initSetters(Lead $lead, Webvork $webvork)
    {
        $webvork->setToken($lead->integration['integration_data_array']['token']);
        $webvork->setOfferId($lead['offer_id']);
        $webvork->setName($lead->order['name']);
        $webvork->setPhone($lead->order['phone']);
        $webvork->setCountry($lead->target_geo_rule['integration_data_array']['country_code']);
        $webvork->setIp($lead['ip']);
    }

    private function isErrorResponse(array $response)
    {
        return empty($response['guid']);
    }
}
