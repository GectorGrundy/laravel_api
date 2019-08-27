<?php
declare(strict_types=1);

use Carbon\Carbon;
use App\Http\GoDataContainer;
use App\Events\Go\SiteVisited;
use Illuminate\Database\Seeder;
use App\Events\Lead\LeadCreated;
use Illuminate\Support\Collection;
use App\Listeners\PublisherStatisticUpdateLeads;
use App\Classes\{
    DeviceInspector, GeoInspector, PhoneInspector
};
use App\Listeners\OnLeadCreated;
use App\Models\{
    Flow, Landing, Lead, Order, Publisher, Transit
};

class TrafficSeeder extends Seeder
{
    /**
     * @var \Faker\Factory
     */
    private $faker;
    /**
     * @var GeoInspector
     */
    private $geo_inspector;
    /**
     * @var Carbon
     */
    private $current_datetime;
    /**
     * @var GoDataContainer
     */
    private $data_container;
    /**
     * @var DeviceInspector
     */
    private $device_inspector;
    /**
     * @var PhoneInspector
     */
    private $phone_inspector;

    public function __construct(
        GeoInspector $geo_inspector,
        DeviceInspector $device_inspector,
        PhoneInspector $phone_inspector
    )
    {
        $this->faker = Faker\Factory::create();
        $this->geo_inspector = $geo_inspector;
        $this->current_datetime = Carbon::now()->subDays(7);
        $this->data_container = app(GoDataContainer::class);
        $this->device_inspector = $device_inspector;
        $this->phone_inspector = $phone_inspector;
    }

    public function run()
    {
        if (app()->environment('production')) {
            $this->error("App in production. You cant do this ( ͡° ͜ʖ ͡°)");
            return;
        }

//        DB::table('hourly_stat')->delete();
//        DB::table('orders')->delete();
//        DB::table('leads')->delete();
//        DB::table('data_stats')->delete();

        /**
         * @var Collection $publishers
         */
        $publishers = Publisher::whereHas('flows')
            ->with(['flows.landings', 'flows.transits', 'flows.offer', 'flows.target.target_geo.country'])
            ->get();

        $visitors_per_second = 0.01;
        $total_visitors = $this->current_datetime->diffInSeconds(Carbon::now()) * $visitors_per_second;
        $this->command->getOutput()->progressStart($total_visitors);

        for ($i = 0; $i < $total_visitors; $i++) {
            for ($j = 0; $j < $visitors_per_second; $j++) {

                $publisher = $publishers->random();
                $flow = $publisher->flows[0];
                $site = $this->getSiteByFlow($flow);

                if ($site instanceof Transit) {
                    $this->visitTransit($site, $flow);
                } else {
                    $this->data_container->setFromTransitId(0);
                    $this->data_container->setFromTransitTrafficType('');
                    $this->visitLanding($site, $flow, $this->getVisitor());
                }
                $this->command->getOutput()->progressAdvance();
            }
            $this->current_datetime->addSecond();
        }

        $this->command->getOutput()->progressFinish();
    }

    private function isLead(): bool
    {
        return random_int(1, 100) < 5;
    }

    private function getSiteByFlow(Flow $flow)
    {
        if (\count($flow->transits)) {
            return $flow->transits->random();
        }

        return $flow->landings->random();
    }

    private function getVisitor()
    {
        $s_id = \Hashids::connection('visitor')->encode([random_int(1, 10000)]);
        $visitor_info = [];
        $ip = $this->faker->ipv4;

        return [
            's_id' => $s_id,
            'info' => $visitor_info,
            'is_fallback' => count($visitor_info) < 1,
            'ip' => $ip,
            'ips' => [$ip],
            'user_agent' => $this->faker->userAgent,
            'is_mobile' => random_int(0, 1),
            'browser_locale' => 'en',
            'geo_ids' => $this->geo_inspector->getGeoIds($ip),
            'referer' => '',
        ];
    }

    private function visitTransit(Transit $transit, Flow $flow)
    {
        $this->data_container->setCurrentDomain($transit->domain);
        $this->data_container->setTransit($transit);
        $this->data_container->setLocale($transit->locale);

        $visitor = $this->getVisitor();
        $this->data_container->setVisitor($visitor);

        $this->setCommonDataContainerParams($flow, $visitor);

        event(new SiteVisited($this->data_container, $this->current_datetime));

        if ($this->shouldGoToLanding()) {
            $this->data_container->setFromTransitId($transit['id']);
            $this->data_container->setFromTransitTrafficType('click');
            $this->visitLanding($flow->landings->random(), $flow, $visitor);
        }
    }

    private function shouldGoToLanding()
    {
        return random_int(1, 80) < 10;
    }

    private function visitLanding(Landing $landing, Flow $flow, array $visitor)
    {
        $this->data_container->setCurrentDomain($landing->domain);
        $this->data_container->setLanding($landing);
        $this->data_container->setLocale($landing->locale);
        $this->data_container->setVisitor($visitor);

        $this->setCommonDataContainerParams($flow, $visitor);

        event(new SiteVisited($this->data_container, $this->current_datetime));

        if ($this->isLead()) {
            $lead = $this->makeOrder();
            $this->processOrder($lead);
        }
    }

    private function makeOrder()
    {
        $name = '';
        if (random_int(1, 10) < 10) {
            $name = $this->faker->name;
        }

        $target_geo = $this->data_container->getVisitorTargetGeo();

        $phone = $this->faker->e164PhoneNumber;
        $phone_info = $this->phone_inspector->checkValid($phone, $target_geo->country['code']);

        $visitor = $this->data_container->getVisitor();
        $landing = $this->data_container->getLanding();
        $flow = $this->data_container->getFlow();
        $device_info = $this->device_inspector->getDeviceIdentifiers($visitor['user_agent']);

        $datetime = $this->current_datetime->toDateTimeString();

        $order_info = (new Order())->createNew([
            'name' => $name,
            'phone' => $phone,
            'email' => $this->faker->email,
            'number_type_id' => $phone_info['number_type'],
            'info' => '{}',
            'products' => '{}',
            'history' => json_encode([[
                'date' => time(),
                'name' => $name,
                'origin_phone' => $phone,
                'phone' => $phone_info['after_processing'],
            ]]),
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);


        $lead = (new Lead())->createNew([
            'domain_id' => $this->data_container->getCurrentDomain()['id'],
            'offer_id' => $landing['offer_id'],
            'target_id' => $target_geo['target_id'],
            'target_geo_id' => $target_geo['id'],
            'country_id' => $target_geo['country_id'],
            'region_id' => $visitor['geo_ids']['region_id'],
            'city_id' => $visitor['geo_ids']['city_id'],
            'publisher_id' => $flow['publisher_id'],
            'landing_id' => $landing['id'],
            'transit_id' => $this->data_container->getFromTransitId(),
            'locale_id' => $this->data_container->getLocale()['id'],
            'flow_id' => $flow['id'],
            'order_id' => $order_info['id'],
            'payout' => $target_geo['payout'],
            'price' => $target_geo['price'],
            'currency_id' => $target_geo['payout_currency_id'],
            'origin' => Lead::WEB_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'initialized_at' => $this->data_container->getFlowClickDate()->toDateTimeString(),
            'is_valid_phone' => $phone_info['is_valid'],
            's_id' => $visitor['s_id'],
            'ip' => $visitor['ip'],
            'ip_country_id' => $visitor['geo_ids']['country_id'],
            'user_agent' => $visitor['user_agent'],
            'data1' => $this->data_container->getData1(),
            'data2' => $this->data_container->getData2(),
            'data3' => $this->data_container->getData3(),
            'data4' => $this->data_container->getData4(),
            'clickid' => $this->data_container->getClickid(),
            'referer' => $visitor['referer'],
            'browser_id' => $device_info['browser_id'],
            'os_platform_id' => $device_info['os_platform_id'],
            'device_type_id' => $device_info['device_type_id'],
            'is_extra_flow' => $this->data_container->isExtraFlow(),
            'transit_traffic_type' => $this->data_container->getFromTransitTrafficType(),
            'browser_locale' => $visitor['browser_locale'],
            'ips' => json_encode($visitor['ips']),
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);

        $event = new LeadCreated($lead);

        (new OnLeadCreated())->handle($event);
        (new PublisherStatisticUpdateLeads())->handle($lead);

        return $lead;
    }

    private function processOrder(Lead $lead)
    {
        $lead->setAsIntegrated($lead->generateExternalKeyById());

        // In real world we have ~
        // - 21% new
        // - 30% approved
        // - 24% cancelled
        // - 27% trashed
        // But I chose random)
        $processed = Carbon::createFromFormat('Y-m-d H:i:s', $lead['created_at']);
        $processed->addMinute(random_int(10, 60));

        switch (array_random([Lead::NEW, Lead::APPROVED, Lead::CANCELLED, Lead::TRASHED])) {
            case Lead::APPROVED:
                $lead->approve(0, '', $processed);
                break;

            case Lead::CANCELLED:
                $lead->cancel(0, '', $processed);
                break;

            case Lead::TRASHED:
                $lead->trash(0, '', $processed);
                break;
        }
    }

    private function setCommonDataContainerParams(Flow $flow, array $visitor)
    {
        $click_date = clone $this->current_datetime;
        $click_date->subMinutes(random_int(1, 10));

        $this->data_container->setFlowClickDate($click_date);
        $this->data_container->setFlow($flow);
        $this->data_container->setOffer($flow->offer);
        $this->data_container->setIsExtraFlow(false);

        $this->data_container->setData1($this->faker->word);
        $this->data_container->setData2($this->faker->word);
        $this->data_container->setData3($this->faker->word);
        $this->data_container->setData4($this->faker->word);
        $this->data_container->setClickid($this->faker->word);
        $this->data_container->setIsBot(false);
        $this->data_container->setFrom('');

        $country_id = (int)$visitor['geo_ids']['country_id'];
        $target_geo = $this->getVisitorTargetGeo($country_id, $flow->target->target_geo);

        $this->data_container->setVisitorTargetGeo($target_geo);
    }

    private function getVisitorTargetGeo(int $country_id, Collection $target_geo_list)
    {
        if (empty($country_id)) {
            return $this->getDefaultTargetGeo($target_geo_list);
        }

        $target_geo = $target_geo_list->where('country_id', $country_id)->first();
        if (\is_null($target_geo)) {
            $target_geo = $this->getDefaultTargetGeo($target_geo_list);
        }

        return $target_geo;
    }

    private function getDefaultTargetGeo(Collection $target_geo_list)
    {
        return $target_geo_list->where('is_default', 1)->first();
    }
}
