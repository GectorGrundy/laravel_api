<?php
declare(strict_types=1);

namespace App\Strategies\LeadCreation;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\{
    Click, Lead, Order, TargetGeo
};

class PixelLeadCreation
{
    use DispatchesJobs;

    public function handle(string $click_hash): Lead
    {
        $order = $this->insertOrder();

        return $this->insertLead($click_hash, $order);
    }

    public function insertOrder(): Order
    {
        return (new Order())->createNew([
            'number_type_id' => Order::PHONE_UNKNOWN,
            'history' => json_encode([])
        ]);
    }

    public function insertLead(string $click_hash, Order $order): Lead
    {
        $click = Click::getByHash($click_hash, ['landing', 'flow']);

        $target_geo = (new TargetGeo())->getById($click['target_geo_id'], [], $click->flow['publisher_id']);

        return (new Lead())->createNew([
            'click_id' => $click['id'],
            'domain_id' => $click['domain_id'],
            'offer_id' => $click->landing['offer_id'],
            'target_id' => $click->landing['target_id'],
            'target_geo_id' => $click['target_geo_id'],
            'country_id' => $click['country_id'],
            'region_id' => $click['region_id'],
            'city_id' => $click['city_id'],
            'publisher_id' => $click->flow['publisher_id'],
            'landing_id' => $click['landing_id'],
            'transit_id' => $click['transit_id'],
            'locale_id' => $click->landing['locale_id'],
            'flow_id' => $click->flow['is_virtual'] ? 0 : $click['flow_id'],
            'order_id' => $order['id'],
            'advertiser_id' => $click['advertiser_id'],
            'advertiser_payout' => $click['advertiser_payout'],
            'advertiser_currency_id' => $click['advertiser_currency_id'],
            'payout' => $target_geo['payout'],
            'price' => $target_geo['price'],
            'currency_id' => $target_geo['payout_currency_id'],
            'origin' => Lead::WEB_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'initialized_at' => $click['initialized_at'],
            'is_valid_phone' => 0,
            's_id' => $click['s_id'],
            'ip' => $click['ip'],
            'ip_country_id' => $click['ip_country_id'],
            'user_agent' => $click['user_agent'],
            'data1' => $click['data1'],
            'data2' => $click['data2'],
            'data3' => $click['data3'],
            'data4' => $click['data4'],
            'clickid' => $click['clickid'],
            'referer' => $click['referer'],
            'browser_id' => $click['browser_id'],
            'os_platform_id' => $click['os_platform_id'],
            'device_type_id' => $click['device_type_id'],
            'is_extra_flow' => $click['is_extra_flow'],
            'transit_traffic_type' => $click['transit_traffic_type'],
            'browser_locale' => $click['browser_locale'],
            'ips' => $click['ips'],
        ]);
    }
}
