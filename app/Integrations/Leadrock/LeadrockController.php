<?php
declare(strict_types=1);

namespace App\Integrations\Leadrock;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Lead;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LeadrockController extends Controller
{
    use PostbackinController;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'external_key' => 'required',
        ]);

        $this->insertLog($request);

        try {
            $integration = $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $lead_external_key = $request->get('external_key');

        try {
            $lead = self::getLeadByExternalKey($integration, $lead_external_key);
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return $this->badLead('external_key');
        }

        request()->merge(['lead' => $lead]);

        if (!$lead->isNew()) {
            return $this->leadAlredyProcessed();
        }

        switch ($request->input('status')) {
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

    private function insertLog(Request $request)
    {
        $log =
            "-----\n"
            . "Action: postback\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . serialize($request->all()) . "\n";

        \File::append(storage_path('logs/leadrock.log'), $log);
    }
}
