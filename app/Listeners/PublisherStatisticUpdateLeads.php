<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\Lead;
use App\Models\PublisherStatistic;

class PublisherStatisticUpdateLeads
{
    public function handle(Lead $lead)
    {
        $datetime = PublisherStatistic::getDatetime($lead['created_at']);

        \DB::insert(
            'INSERT INTO `publisher_statistics`
                    SET `publisher_id`= :publisher_id,
                        `flow_id`     = :flow_id,
                        `currency_id` = :currency_id,
                        `datetime`    = :datetime,
                        `leads`       = :leads
        		    ON DUPLICATE KEY UPDATE
        			    `leads`       = `leads` + :upd_leads',
            [
                'publisher_id' => $lead['publisher_id'],
                'flow_id' => $lead['flow_id'],
                'currency_id' => $lead['currency_id'],
                'datetime' => $datetime,
                'leads' => 1,
                'upd_leads' => 1,
            ]
        );
    }
}
