<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\{
    Landing, Flow, TempLead
};
use Carbon\Carbon;

class TempLeadService
{
    public static function isDouble(Flow $flow, Landing $landing, string $phone, string $s_id)
    {
        $five_minutes_ago = Carbon::now()->subMinutes(5)->toDateTimeString();

        return TempLead::where('flow_id', $flow->id)
            ->where('landing_id', $landing->id)
            ->where('phone', $phone)
            ->where('s_id', $s_id)
            ->createdFrom($five_minutes_ago)
            ->exists();
    }
}
