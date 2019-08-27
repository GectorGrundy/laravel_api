<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\TempLead;
use App\Services\LeadDoubleValidator;
use App\Strategies\LeadCreation\TempLeadCreation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LeadCreateFromTemp extends Command
{
    protected $signature = 'lead:create_from_temp';
    protected $description = 'Create cod lead using temp lead';

    public function handle()
    {
        $ten_minutes_ago = Carbon::now()->subMinutes(10)->toDateTimeString();

        // Сессии, по которым можно генерировать лид
        $sessions = TempLead::distinct()
            ->select('s_id')
            ->doesntHaveLead()
            ->createdTo($ten_minutes_ago)
            ->groupBy('s_id')
            ->get();

        if (!$sessions->count()) {
            return;
        }

        /**
         * @var TempLeadCreation $temp_lead_creation
         */
        $temp_lead_creation = app(TempLeadCreation::class);

        foreach ($sessions as $session) {

            // Самый новый лид с самым длинным номером телефона в сессии
            $temp_lead = TempLead::with(['target_geo'])
                ->session($session->s_id)
                ->orderByRaw(\DB::raw('LENGTH(phone) DESC'))
                ->latest('id')
                ->first();

            $double_lead_hash = LeadDoubleValidator::getLeadForParams(
                $temp_lead['phone'],
                $temp_lead->target_geo['hash']
            );

            if (!is_null($double_lead_hash)) {
                TempLead::closeBySID($session->s_id);

            } else {
                $lead = $temp_lead_creation->handle($temp_lead);
                $temp_lead->lead()->associate($lead)->save();
                TempLead::closeBySID($session->s_id, $temp_lead->id);
            }
        }
    }
}
