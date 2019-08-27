<?php
declare(strict_types=1);

namespace App\Strategies\LeadCreation;

use App\Models\{
    Lead, Order
};
use Illuminate\Http\Request;
use App\Classes\{
    PhoneInspector, DeviceInspector
};
use App\Services\PublisherApiDataContainer;

class ApiLeadCreation
{
    /**
     * @var Request
     */
    private $request;
    private $phone_inspector;
    private $target_geo;
    private $flow;
    private $device_inspector;
    private $phone_info;
    private $ip;
    private $geo;
    private $device_info;

    public function __construct(PhoneInspector $phone_inspector, DeviceInspector $device_inspector)
    {
        $this->phone_inspector = $phone_inspector;
        $this->device_inspector = $device_inspector;
    }

    public function handle(Request $request): Lead
    {
        $this->request = $request;
        $this->flow = $this->request->input('flow');
        $this->target_geo = $this->request->input('target_geo');
        $this->ip = $this->request->input('ip');
        $this->geo = $this->request->input('geo');
        $this->device_info = $this->device_inspector->getDeviceIdentifiers($request->input('user_agent'));
        $this->phone_info = $this->phone_inspector->checkValid(
            $request->input('phone'),
            $this->target_geo->country['code']
        );

        $order = $this->insertOrder();

        return $this->insertLead($order);
    }


    private function insertOrder(): Order
    {
        return (new Order())->createNew([
            'name' => $this->request->input('name'),
            'phone' => $this->phone_info['after_processing'],
            'number_type_id' => $this->phone_info['number_type'],
            'products' => $this->request->input('products', '{}'),
            'history' => json_encode(new \stdClass()),
        ]);

    }

    private function insertLead(Order $order): Lead
    {
        /**
         * @var PublisherApiDataContainer $data_container
         */
        $data_container = app(PublisherApiDataContainer::class);

        $publisher = $data_container->getPublisher();

        return (new Lead())->createNew([
            'offer_id' => $this->target_geo->target['offer_id'],
            'target_id' => $this->target_geo['target_id'],
            'target_geo_id' => $this->target_geo['id'],
            'country_id' => $this->target_geo['country_id'],
            'region_id' => $this->geo['region_id'],
            'city_id' => $this->geo['city_id'],
            'publisher_id' => $publisher['id'],
            'locale_id' => $this->target_geo->target['locale_id'],
            'flow_id' => $this->flow['id'],
            'order_id' => $order['id'],
            'price' => $this->target_geo['price'],
            'payout' => $this->target_geo['payout'],
            'currency_id' => $this->target_geo['payout_currency_id'],
            'hold_time' => $this->target_geo['hold_time'],
            'origin' => Lead::API_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'initialized_at' => date('Y-m-d H:i:s'),
            'is_valid_phone' => (int)$this->phone_info['is_valid'],
            'ip' => $this->ip,
            'ip_country_id' => $this->geo['country_id'],
            'user_agent' => mb_substr($this->request->input('user_agent', ''), 0, 255),
            'referer' => mb_substr($this->request->input('referer', ''), 0, 255),
            'browser_id' => $this->device_info['browser_id'],
            'os_platform_id' => $this->device_info['os_platform_id'],
            'device_type_id' => $this->device_info['device_type_id'],
            'ips' => json_encode(new \stdClass()),
            'data1' => $this->request->input('data1', ''),
            'data2' => $this->request->input('data2', ''),
            'data3' => $this->request->input('data3', ''),
            'data4' => $this->request->input('data4', ''),
        ]);
    }
}
