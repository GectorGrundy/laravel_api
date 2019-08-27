<?php
declare(strict_types=1);

namespace App\Integrations\Cpawebnet;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Traits\PostbackinController;
use App\Exceptions\Hashids\NotDecodedHashException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CpawebnetController extends Controller
{
    use PostbackinController;

    public const NEW = 2;
    public const APPROVED = 1;
    public const CANCELLED = 3;

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'sub1' => 'required',
            'status' => 'required'
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

        request()->merge(['lead' => $lead]);

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        switch ($request->get('status')) {
            case self::NEW:
                break;

            case self::APPROVED:
                $lead->approve();
                break;

            case self::CANCELLED:
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

        \File::append(storage_path('logs/cpawebnet.log'), $log);
    }
}
