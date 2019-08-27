<?php
declare(strict_types=1);

namespace App\Integrations\Affbay;

use App\Models\Lead;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AffbayController extends Controller
{
    use PostbackinController;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'status' => 'required|in:' . Lead::APPROVED . ',' . Lead::CANCELLED,
            'lead_hash' => 'required',
        ]);
        
        $this->insertFileLog($request);

        try {
            $integration = $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        try {
            $lead = self::getLeadByHash($integration, $request->input('lead_hash'));
        } catch (ModelNotFoundException $e) {
            return $this->badLead('lead_hash');
        }

        request()->merge(['lead' => $lead]);

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        switch ($request->get('status')) {
            case Lead::APPROVED:
                $lead->approve();
                break;

            case Lead::CANCELLED:
                $lead->cancel();
                break;

            default:
                return $this->incorrectNewLeadStatus();
        }

        return $this->ok();
    }

    private function insertFileLog(Request $request)
    {
        $log =
            "-----\n"
            . "Action: postback\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . serialize($request->all()) . "\n";

        \File::append(storage_path('logs/affbay.log'), $log);
    }
}
