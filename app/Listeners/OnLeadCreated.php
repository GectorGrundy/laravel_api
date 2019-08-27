<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Lead\LeadCreated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\{
    DeviceStat, Lead, HourlyStat, TargetGeoStat
};

class OnLeadCreated implements ShouldQueue
{
    use SerializesModels;

    public $queue = 'ninja';

    public function handle(LeadCreated $event)
    {
        \DB::transaction(function () use ($event) {
            /**
             * @var Lead $lead
             */
            $lead = $event->lead->fresh();

            HourlyStat::onLeadCreated($lead);
            DeviceStat::onLeadCreated($lead);
            TargetGeoStat::onLeadCreated($lead);

            (new PublisherStatisticUpdateLeads())->handle($lead);
            (new IntegrateCodLead())->handle($lead);
        });
    }
}
