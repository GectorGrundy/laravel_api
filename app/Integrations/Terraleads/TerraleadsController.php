<?php
declare(strict_types=1);

namespace App\Integrations\Terraleads;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Lead;
use App\Http\Controllers\Controller;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TerraleadsController extends Controller
{
    use PostbackinController;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'sub_id' => 'required',
        ]);

        $this->insertLog($request);

        try {
           $integration =  $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $lead_hash = $request->get('sub_id');

        try {
            $lead = self::getLeadByHash($integration, $lead_hash);
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return $this->badLead('sub_id');
        }

        request()->merge(['lead' => $lead]);

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        switch (config("integrations.terraleads.status_pairs.{$request->get('status')}")) {
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

        return response('OK');
    }

    private function insertLog(Request $request)
    {
        $log =
            "-----\n"
            . "Action: postback\n"
            . 'Date: ' . date('d.m.Y H:i:s') . "\n"
            . 'Request: ' . serialize($request->all()) . "\n";

        \File::append(storage_path('/logs/terraleads.log'), $log);
    }
}
