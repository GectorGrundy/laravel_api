<?php
declare(strict_types=1);

namespace App\Integrations\Leadbit;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Lead;
use App\Http\Controllers\Controller;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class LeadbitController extends Controller
{
    use PostbackinController;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'sub1' => 'required',
        ]);

        $this->insertLog($request);

        try {
            $integration = $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $lead_hash = $request->get('sub1');

        try {
            $lead = self::getLeadByHash($integration, $lead_hash);
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return $this->badLead('sub1');
        }

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        request()->merge(['lead' => $lead]);

        switch (config("integrations.leadbit.status_pairs.{$request->get('status')}")) {
            case Lead::NEW:
                break;

            case Lead::APPROVED:
                $lead->approve();
                break;

            case Lead::CANCELLED:
                $lead->cancel();
                break;

            case Lead::TRASHED:
                $lead->trash();
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

        \File::append(storage_path('/logs/leadbit.log'), $log);
    }
}
