<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\{
    Flow, Landing, Transit
};
use Illuminate\Bus\Queueable;
use App\Events\Go\SiteVisited;
use App\Services\VisitorUnique;
use App\Classes\DeviceInspector;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class StatOnSiteVisited implements ShouldQueue
{
    use Queueable;
    use SerializesModels;
    use InteractsWithQueue;

    /**
     * @var bool
     */
    private $is_bot;
    /**
     * @var bool
     */
    private $is_traffback;
    /**
     * @var bool
     */
    private $is_safepage;
    /**
     * @var bool
     */
    private $is_transit_unique;
    /**
     * @var SiteVisited
     */
    private $event;
    /**
     * @var array
     */
    private $landing_unique_data;
    /**
     * @var bool
     */
    private $is_flow_unique;
    /**
     * @var bool
     */
    private $is_publisher_unique;
    /**
     * @var Flow
     */
    private $flow;
    /**
     * @var bool
     */
    private $is_offer_unique;
    /**
     * @var bool
     */
    private $is_system_unique;
    /**
     * @var int
     */
    private $transit_id;
    /**
     * @var int
     */
    private $landing_id;
    /**
     * @var array
     */
    private $visitor;
    /**
     * @var DeviceInspector
     */
    private $device_inspector;
    /**
     * @var string
     */
    private $datetime;

    public function __construct(DeviceInspector $device_inspector)
    {
        $this->device_inspector = $device_inspector;
    }

    public function handle(SiteVisited $event)
    {
        $this->event = $event;
        $this->datetime = $this->event->date . ' ' . $this->event->hour . ':00:00';
        $this->flow = $event->data_container->getFlow();
        $this->visitor = $event->data_container->getVisitor();
        $this->is_bot = $event->data_container->isBot();
        $this->is_traffback = $event->data_container->isTraffback();
        $this->is_safepage = $event->data_container->isSafepage();
        $from = $event->data_container->getFrom();
        $site = $event->data_container->getSite();

        $visitor_unique = new VisitorUnique();

        if ($site instanceof Transit) {
            $this->landing_id = 0;
            $this->transit_id = $site['id'];
            $this->is_transit_unique = $visitor_unique->getTransitUnique(
                $this->visitor['info'],
                $this->flow,
                $this->transit_id
            );

        } elseif ($site instanceof Landing) {
            $this->landing_id = $site['id'];
            $this->transit_id = $event->data_container->getFromTransitId();
            $this->is_transit_unique = false;

        } else {
            throw new \LogicException('Unknown type of site.');
        }

        $this->is_flow_unique = $visitor_unique->getFlowUnique($this->visitor['info'], $this->flow);
        $this->is_publisher_unique = $visitor_unique->getPublisherUnique($this->visitor['info'], $this->flow);
        $this->is_offer_unique = $visitor_unique->getOfferUnique($this->visitor['info'], $this->flow);
        $this->is_system_unique = $visitor_unique->getSystemUnique($this->visitor['info']);
        $this->landing_unique_data = $visitor_unique->getLandingUnique(
            $this->visitor['info'],
            $this->flow,
            $from,
            $this->landing_id,
            $this->transit_id
        );

        $this->updateHourlyStat();
        $this->updateDeviceStat();
        $this->updateTargetGeoStat();
    }

    private function updateHourlyStat()
    {
        \DB::insert(
            'INSERT INTO `hourly_stat`
				SET `datetime`          = :datetime,
					`flow_id`           = :flow_id,
					`publisher_id`      = :publisher_id,
					`offer_id`          = :offer_id,
					`transit_id`        = :transit_id,
					`landing_id`        = :landing_id,
					`country_id`        = :country_id,
					`region_id`         = :region_id,
					`city_id`           = :city_id,
					`currency_id`       = :currency_id,
					`target_geo_country_id`= :target_geo_country_id,
					`data1`     	    = :data1,
					`data2`     	    = :data2,
					`data3`     	    = :data3,
					`data4`     	    = :data4,
					`hits`              = :hits,
					`bot_count`         = :bot_count,
					`traffback_count`   = :traffback_count,
					`safepage_count`    = :safepage_count,
					`transit_hosts`     = :transit_hosts,
					`transit_landing_count` = :transit_landing_count,
					`transit_landing_hosts` = :transit_landing_hosts,
					`direct_landing_hosts`  = :direct_landing_hosts,
					`noback_landing_hosts`  = :noback_landing_hosts,
					`comeback_landing_hosts`= :comeback_landing_hosts,
					`flow_hosts`            = :flow_hosts,
					`publisher_hosts`       = :publisher_hosts,
					`offer_hosts`           = :offer_hosts,
					`system_hosts`          = :system_hosts
				ON DUPLICATE KEY UPDATE
				    `hits`                  = (`hits`                   + :upd_hits),
					`bot_count`             = (`bot_count`              + :upd_bot_count),
					`traffback_count`       = (`traffback_count`        + :upd_traffback_count),
					`safepage_count`        = (`safepage_count`         + :upd_safepage_count),
					`transit_hosts`         = (`transit_hosts`          + :upd_transit_hosts),
					`transit_landing_count` = (`transit_landing_count`  + :upd_transit_landing_count),
					`transit_landing_hosts` = (`transit_landing_hosts`  + :upd_transit_landing_hosts),
					`direct_landing_hosts`  = (`direct_landing_hosts`   + :upd_direct_landing_hosts),
					`noback_landing_hosts`  = (`noback_landing_hosts`   + :upd_noback_landing_hosts),
					`comeback_landing_hosts`= (`comeback_landing_hosts` + :upd_comeback_landing_hosts),
					`flow_hosts`            = (`flow_hosts`             + :upd_flow_hosts),
					`publisher_hosts`       = (`publisher_hosts`        + :upd_publisher_hosts),
					`offer_hosts`           = (`offer_hosts`            + :upd_offer_hosts),
					`system_hosts`          = (`system_hosts`           + :upd_system_hosts);',
            [
                'datetime' => $this->datetime,
                'flow_id' => $this->flow['is_virtual'] ? 0 : $this->flow['id'],
                'publisher_id' => $this->flow['publisher_id'],
                'offer_id' => $this->flow['offer_id'],
                'transit_id' => $this->transit_id,
                'landing_id' => $this->landing_id,
                'country_id' => $this->visitor['geo_ids']['country_id'],
                'region_id' => $this->visitor['geo_ids']['region_id'],
                'city_id' => $this->visitor['geo_ids']['city_id'],
                'currency_id' => $this->event->data_container->getVisitorTargetGeo()->getPublisherCurrencyId(),
                'target_geo_country_id' => $this->event->data_container->getVisitorTargetGeo()['country_id'],
                'data1' => $this->event->data_container->getData1(),
                'data2' => $this->event->data_container->getData2(),
                'data3' => $this->event->data_container->getData3(),
                'data4' => $this->event->data_container->getData4(),

                'hits' => !$this->is_bot,
                'upd_hits' => !$this->is_bot,

                'bot_count' => $this->is_bot,
                'upd_bot_count' => $this->is_bot,

                'traffback_count' => $this->is_traffback,
                'upd_traffback_count' => $this->is_traffback,

                'safepage_count' => $this->is_safepage,
                'upd_safepage_count' => $this->is_safepage,

                'transit_hosts' => $this->is_transit_unique && !$this->is_bot,
                'upd_transit_hosts' => $this->is_transit_unique && !$this->is_bot,

                'transit_landing_count' => $this->landing_unique_data['transit_landing_rel_unique'] && !$this->is_bot,
                'upd_transit_landing_count' => $this->landing_unique_data['transit_landing_rel_unique'] && !$this->is_bot,

                'transit_landing_hosts' => $this->landing_unique_data['transit_landing_unique'] && !$this->is_bot,
                'upd_transit_landing_hosts' => $this->landing_unique_data['transit_landing_unique'] && !$this->is_bot,

                'direct_landing_hosts' => $this->landing_unique_data['direct_landing_unique'] && !$this->is_bot,
                'upd_direct_landing_hosts' => $this->landing_unique_data['direct_landing_unique'] && !$this->is_bot,

                'noback_landing_hosts' => $this->landing_unique_data['noback_landing_unique'] && !$this->is_bot,
                'upd_noback_landing_hosts' => $this->landing_unique_data['noback_landing_unique'] && !$this->is_bot,

                'comeback_landing_hosts' => $this->landing_unique_data['comeback_landing_unique'] && !$this->is_bot,
                'upd_comeback_landing_hosts' => $this->landing_unique_data['comeback_landing_unique'] && !$this->is_bot,

                'flow_hosts' => $this->is_flow_unique && !$this->is_bot,
                'upd_flow_hosts' => $this->is_flow_unique && !$this->is_bot,

                'publisher_hosts' => $this->is_publisher_unique && !$this->is_bot,
                'upd_publisher_hosts' => $this->is_publisher_unique && !$this->is_bot,

                'offer_hosts' => $this->is_offer_unique && !$this->is_bot,
                'upd_offer_hosts' => $this->is_offer_unique && !$this->is_bot,

                'system_hosts' => $this->is_system_unique && !$this->is_bot,
                'upd_system_hosts' => $this->is_system_unique && !$this->is_bot,
            ]
        );
    }

    private function updateDeviceStat()
    {
        $device = $this->device_inspector->getDeviceIdentifiers($this->visitor['user_agent']);

        \DB::insert(
            'INSERT INTO `device_stat`
				SET `datetime`          = :datetime,
					`flow_id`           = :flow_id,
					`publisher_id`      = :publisher_id,
					`offer_id`          = :offer_id,
					`country_id`        = :country_id,
					`browser_id`        = :browser_id,
					`os_platform_id`        = :os_platform_id,
					`device_type_id`    = :device_type_id,
					`currency_id`       = :currency_id,
					`target_geo_country_id`= :target_geo_country_id,
					`landing_id`        = :landing_id,
					`transit_id`        = :transit_id,
					`data1`     	    = :data1,
					`data2`     	    = :data2,
					`data3`     	    = :data3,
					`data4`     	    = :data4,
					`hits`              = :hits,
					`bot_count`         = :bot_count,
					`traffback_count`   = :traffback_count,
					`safepage_count`    = :safepage_count,
					`flow_hosts`            = :flow_hosts,
					`publisher_hosts`       = :publisher_hosts,
					`offer_hosts`           = :offer_hosts,
					`system_hosts`          = :system_hosts
				ON DUPLICATE KEY UPDATE
				    `hits`                  = (`hits`                   + :upd_hits),
					`bot_count`             = (`bot_count`              + :upd_bot_count),
					`traffback_count`       = (`traffback_count`        + :upd_traffback_count),
					`safepage_count`        = (`safepage_count`         + :upd_safepage_count),
					`flow_hosts`            = (`flow_hosts`             + :upd_flow_hosts),
					`publisher_hosts`       = (`publisher_hosts`        + :upd_publisher_hosts),
					`offer_hosts`           = (`offer_hosts`            + :upd_offer_hosts),
					`system_hosts`          = (`system_hosts`           + :upd_system_hosts);',
            [
                'datetime' => $this->datetime,
                'flow_id' => $this->flow['is_virtual'] ? 0 : $this->flow['id'],
                'publisher_id' => $this->flow['publisher_id'],
                'offer_id' => $this->flow['offer_id'],
                'browser_id' => $device['browser_id'],
                'os_platform_id' => $device['os_platform_id'],
                'device_type_id' => $device['device_type_id'],
                'country_id' => $this->visitor['geo_ids']['country_id'],
                'currency_id' => $this->event->data_container->getVisitorTargetGeo()->getPublisherCurrencyId(),
                'target_geo_country_id' => $this->event->data_container->getVisitorTargetGeo()['country_id'],
                'transit_id' => $this->transit_id,
                'landing_id' => $this->landing_id,

                'data1' => $this->event->data_container->getData1(),
                'data2' => $this->event->data_container->getData2(),
                'data3' => $this->event->data_container->getData3(),
                'data4' => $this->event->data_container->getData4(),

                'hits' => !$this->is_bot,
                'upd_hits' => !$this->is_bot,

                'bot_count' => $this->is_bot,
                'upd_bot_count' => $this->is_bot,

                'traffback_count' => $this->is_traffback,
                'upd_traffback_count' => $this->is_traffback,

                'safepage_count' => $this->is_safepage,
                'upd_safepage_count' => $this->is_safepage,

                'flow_hosts' => $this->is_flow_unique && !$this->is_bot,
                'upd_flow_hosts' => $this->is_flow_unique && !$this->is_bot,

                'publisher_hosts' => $this->is_publisher_unique && !$this->is_bot,
                'upd_publisher_hosts' => $this->is_publisher_unique && !$this->is_bot,

                'offer_hosts' => $this->is_offer_unique && !$this->is_bot,
                'upd_offer_hosts' => $this->is_offer_unique && !$this->is_bot,

                'system_hosts' => $this->is_system_unique && !$this->is_bot,
                'upd_system_hosts' => $this->is_system_unique && !$this->is_bot,
            ]
        );
    }

    private function updateTargetGeoStat()
    {
        \DB::insert(
            'INSERT INTO `target_geo_stats`
				SET `datetime`          = :datetime,
					`target_geo_id`     = :target_geo_id,
					`currency_id`       = :currency_id,
					`hits`              = :hits,
					`flow_hosts`        = :flow_hosts
				ON DUPLICATE KEY UPDATE
				    `hits`              = (`hits`       + :upd_hits),
				    `flow_hosts`        = (`flow_hosts` + :upd_flow_hosts);',

            [
                'datetime' => $this->datetime,
                'target_geo_id' => $this->event->data_container->getVisitorTargetGeo()['id'],
                'currency_id' => $this->event->data_container->getVisitorTargetGeo()->getPublisherCurrencyId(),

                'hits' => !$this->is_bot,
                'upd_hits' => !$this->is_bot,

                'flow_hosts' => $this->is_flow_unique && !$this->is_bot,
                'upd_flow_hosts' => $this->is_flow_unique && !$this->is_bot,

            ]
        );
    }
}
