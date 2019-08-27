<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Lead;
use App\Strategies\LeadCreation\PixelLeadCreation;

class CreatePixelLead extends Controller
{
    /**
     * @var PixelLeadCreation
     */
    private $lead_creation;

    public function __construct(PixelLeadCreation $lead_creation)
    {
        $this->lead_creation = $lead_creation;
    }

    public function __invoke()
    {
        $click_hash = $this->getClickid();
        if (\is_null($click_hash)) {
            return ['status' => 'error', 'message' => 'Incorrect clickid'];
        }

        $click_id = \Hashids::decode($click_hash)[0] ?? 0;
        if (empty($click_id)) {
            return ['status' => 'error', 'message' => 'Incorrect clickid'];
        }

        if (Lead::existsByClickId($click_id)) {
            return ['status' => 'error', 'message' => 'Lead has already created'];
        }

        $lead = $this->lead_creation->handle($click_hash);

        return ['status' => 'ok', 'lead_hash' => $lead['hash']];
    }

    private function getClickid(): ?string
    {
        if (request()->filled('clickid')) {
            return request('clickid');
        }
        return request()->cookie('clickid');
    }
}
