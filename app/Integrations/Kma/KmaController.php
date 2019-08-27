<?php
declare(strict_types=1);

namespace App\Integrations\Kma;

use App\Exceptions\Hashids\NotDecodedHashException;
use App\Models\Lead;
use App\Models\Traits\PostbackinController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KmaController extends Controller
{
    use PostbackinController;

    public const NEW = 'P';
    public const APPROVED = 'A';
    public const CANCELLED = 'D';
    public const INVALID = 'F';

    public function postback(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            'data1' => 'required',
        ]);

        $this->insertLog($request);

        try {
            $integration = $this->getIntegration($request);
        } catch (ModelNotFoundException $e) {
            return $this->badApiKey();
        }

        $lead_hash = $request->get('data1');

        try {
            $lead = self::getLeadByHash($integration, $lead_hash);
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return $this->badLead('data1');
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

        \File::append(storage_path('logs/kma.log'), $log);
    }
}
