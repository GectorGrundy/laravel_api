<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\Go\SiteVisited;
use App\Models\PublisherStatistic;
use App\Services\VisitorUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class PublisherStatisticUpdateHosts implements ShouldQueue
{
    use SerializesModels;

    public function handle(SiteVisited $event)
    {
        $flow = $event->data_container->getFlow();
        $visitor = $event->data_container->getVisitor();
        $target_geo = $event->data_container->getVisitorTargetGeo();

        $flow_unique = (new VisitorUnique())->getFlowUnique($visitor['info'], $flow);
        if (!$flow_unique) {
            return;
        }

        $datetime = PublisherStatistic::getDatetime();

        \DB::insert(
            'INSERT INTO `publisher_statistics`
                    SET `publisher_id`= :publisher_id,
                        `flow_id`     = :flow_id,
                        `currency_id` = :currency_id,
                        `datetime`    = :datetime,
                        `hosts`       = :hosts
        		    ON DUPLICATE KEY UPDATE
        			    `hosts`       = `hosts` + :upd_hosts',
            [
                'publisher_id' => $flow['publisher_id'],
                'flow_id' =>$flow['id'],
                'currency_id' => $target_geo->getPublisherCurrencyId(),
                'datetime' => $datetime,
                'hosts' => 1,
                'upd_hosts' => 1,
            ]
        );
    }
}
