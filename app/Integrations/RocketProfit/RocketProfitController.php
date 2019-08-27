<?php
declare(strict_types=1);

namespace App\Integrations\RocketProfit;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Lead;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RocketProfitController extends Controller
{
    use PostbackinController;

    public const NEW = 'pending';
    public const APPROVED = 'approved';
    public const CANCELLED = 'rejected';
    public const HOLD = 'pending';
    public const INVALID = 'trash';

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'subid1' => 'required',
        ]);

        $this->insertLog($request);

        try {
           $integration =  $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $lead_hash = $request->get('subid1');

        try {
            $lead = self::getLeadByHash($integration, $lead_hash);
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return $this->badLead('subid1');
        }

        request()->merge(['lead' => $lead]);

        if ($lead->status !== Lead::NEW) {
            return $this->leadAlredyProcessed();
        }

        switch ($request->get('status')) {
            case self::NEW:
            case self::HOLD:
                break;

            case self::APPROVED:
                $lead->approve();
                break;

            case self::CANCELLED:
                $lead->cancel();
                break;

            case self::INVALID:
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

        \File::append(storage_path('logs/rocketprofit.log'), $log);
    }
}
