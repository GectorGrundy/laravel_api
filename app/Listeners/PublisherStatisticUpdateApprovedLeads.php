<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Event;
use App\Events\Lead\LeadApproved;
use App\Events\Lead\LeadStateEvent;
use App\Models\PublisherStatistic;

class PublisherStatisticUpdateApprovedLeads
{
    public function handle(LeadStateEvent $event)
    {
        $datetime = PublisherStatistic::getDatetime($event->lead['created_at']);

        $sign = $this->getSignByEvent($event);

        \DB::insert(
            "INSERT INTO `publisher_statistics`
                    SET `publisher_id`  = :publisher_id,
                        `flow_id`       = :flow_id,
                        `currency_id`   = :currency_id,
                        `datetime`      = :datetime,
                        `approved_leads`= {$sign}:approved_leads
        		    ON DUPLICATE KEY UPDATE
        			    `approved_leads`= `approved_leads` {$sign} :upd_approved_leads",
            [
                'publisher_id' => $event->lead->publisher_id,
                'flow_id' => $event->lead->flow_id,
                'currency_id' => $event->lead->currency_id,
                'datetime' => $datetime,
                'approved_leads' => 1,
                'upd_approved_leads' => 1,
            ]
        );
    }

    private function getSignByEvent(Event $event): string
    {
        if ($event instanceof LeadApproved) {
            return '+';
        }

        return '-';
    }
}
