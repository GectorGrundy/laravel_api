<?php
declare(strict_types=1);

namespace App\Integrations\Adcombo;

use App\Models\Lead;
use App\Models\Traits\PostbackinController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdcomboController extends Controller
{
    use PostbackinController;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'PARAM' => 'required',
            'status' => 'required|in:' . Lead::APPROVED . ',' . Lead::CANCELLED
        ]);

        $this->insertLog($request);

        try {
            $integration = $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $external_key = $request->get('PARAM');

        try {
            $lead = self::getLeadByExternalKey($integration, $external_key);
        } catch (ModelNotFoundException $e) {
            return $this->badLead('PARAM');
        }

        request()->merge(['lead' => $lead]);

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        switch ($request->get('status')) {
            case Lead::NEW:
                break;

            case Lead::APPROVED:
                $lead->approve();
                break;

            case Lead::CANCELLED:
                $lead->cancel();
                break;

            default:
                return $this->incorrectNewLeadStatus();
        }

        return response('OK');
    }

    private function insertLog(Request $request)
    {
        $log =
            "-----\n"
            . "Action: postback\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . serialize($request->all()) . "\n";

        \File::append(storage_path('/logs/adcombo.log'), $log);
    }
}
