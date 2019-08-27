<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Integration;
use App\Models\Lead;
use Illuminate\Http\Request;

trait PostbackinController
{
    public function getIntegration(Request $request)
    {
        return (new Integration())->getByInternalApiKey($request->input('api_key'));
    }

    public static function getLeadByHash(Integration $integration, string $hash): Lead
    {
        return Lead::whereIntegration($integration)->where('hash', $hash)->firstOrFail();
    }

    public static function getLeadByExternalKey(Integration $integration, string $external_key): Lead
    {
        return Lead::whereIntegration($integration)->where('external_key', $external_key)->firstOrFail();
    }

    public function badLead(string $lead_param)
    {
        return [
            'status' => 'error',
            'message' => "Bad {$lead_param} parameter.",
        ];
    }

    public function leadAlredyProcessed()
    {
        return [
            'status' => 'error',
            'message' => 'Specified order have been already processed.',
        ];
    }

    public function badApiKey()
    {
        return [
            'status' => 'error',
            'message' => 'Incorrect api_key.',
        ];
    }

    public function incorrectNewLeadStatus()
    {
        return [
            'status' => 'error',
            'message' => 'Incorrect new order status.',
        ];
    }

    public function ok()
    {
        return ['status' => 'ok'];
    }
}