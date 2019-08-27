<?php
declare(strict_types=1);

namespace App\Strategies\LeadCreation;

use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\{
    Lead, Order, TargetGeo
};

class CodCorrectLeadCreation
{
    use DispatchesJobs;

    public function handle(Lead $origin_lead, TargetGeo $target_geo, array $phone_info, $name): Lead
    {
        $order = $this->insertOrder($origin_lead, $phone_info, $name);
        return $this->insertLead($origin_lead, $target_geo, $phone_info, $order);
    }

    public function insertOrder(Lead $origin_lead, array $phone_info, string $name)
    {
        return (new Order())->createNew([
            'name' => $name,
            'phone' => $phone_info['after_processing'],
            'info' => $origin_lead->order['info'],
            'products' => $origin_lead->order['products'],
            'number_type_id' => $phone_info['number_type'],
            'history' => json_encode([[
                'date' => time(),
                'name' => $name,
                'origin_phone' => $phone_info['origin'],
                'phone' => $phone_info['after_processing'],
            ]])
        ]);
    }

    public function insertLead(Lead $origin_lead, TargetGeo $target_geo, array $phone_info, Order $order): Lead
    {
        return (new Lead())->createNew([
            'origin_lead_id' => $origin_lead['id'],
            'domain_id' => $origin_lead['domain_id'],
            'offer_id' => $origin_lead['offer_id'],
            'target_id' => $target_geo['target_id'],
            'target_geo_id' => $target_geo['id'],
            'country_id' => $target_geo['country_id'],
            'region_id' => $origin_lead['region_id'],
            'city_id' => $origin_lead['city_id'],
            'publisher_id' => $origin_lead['publisher_id'],
            'landing_id' => $origin_lead['landing_id'],
            'transit_id' => $origin_lead['transit_id'],
            'locale_id' => $origin_lead['locale_id'],
            'flow_id' => $origin_lead['flow_id'],
            'order_id' => $order['id'],
            'payout' => $target_geo['payout'],
            'price' => $target_geo['price'],
            'currency_id' => $target_geo['payout_currency_id'],
            'origin' => Lead::WEB_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'initialized_at' => $origin_lead['initialized_at'],
            'is_valid_phone' => $phone_info['is_valid'],
            's_id' => $origin_lead['s_id'],
            'ip' => $origin_lead['ip'],
            'ip_country_id' => $origin_lead['ip_country_id'],
            'user_agent' => $origin_lead['user_agent'],
            'data1' => $origin_lead['data1'],
            'data2' => $origin_lead['data2'],
            'data3' => $origin_lead['data3'],
            'data4' => $origin_lead['data4'],
            'clickid' => $origin_lead['clickid'],
            'referer' => $origin_lead['referer'],
            'browser_id' => $origin_lead['browser_id'],
            'os_platform_id' => $origin_lead['os_platform_id'],
            'device_type_id' => $origin_lead['device_type_id'],
            'is_extra_flow' => $origin_lead['is_extra_flow'],
            'transit_traffic_type' => $origin_lead['transit_traffic_type'],
            'browser_locale' => $origin_lead['browser_locale'],
            'ips' => $origin_lead['ips'],
        ]);
    }
}
