<?php
declare(strict_types=1);

namespace App\Strategies\LeadCreation;

use App\Classes\DeviceInspector;
use App\Classes\PhoneInspector;
use App\Http\GoDataContainer;
use App\Http\Requests\Go\GoRequest;
use App\Services\LeadDoubleValidator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request AS IlluminateRequest;
use App\Models\{
    Lead, Order, TargetGeo, TempLead
};

/**
 * Класс для обработки запроса на генерацию cod лида
 */
class CodLeadCreation
{
    use DispatchesJobs;

    private $request;
    private $go_request;
    private $target_geo;
    private $phone_inspector;
    private $device_inspector;
    private $lead;
    private $name;
    private $address;
    private $email;
    private $products;
    private $phone;
    private $order;
    private $phone_info;
    private $data_container;
    private $contact_type;
    private $custom;

    public function __construct(
        IlluminateRequest $request, GoRequest $go_request, TargetGeo $target_geo, PhoneInspector $phone_inspector,
        DeviceInspector $device_inspector, Lead $lead, Order $order, GoDataContainer $data_container
    )
    {
        $this->request = $request;
        $this->go_request = $go_request;
        $this->target_geo = $target_geo;
        $this->phone_inspector = $phone_inspector;
        $this->device_inspector = $device_inspector;
        $this->lead = $lead;
        $this->order = $order;
        $this->data_container = $data_container;
    }

    public function handle()
    {
        // @todo validate target_geo_hash, products (json), phone, name(string, cut to 255 symbols)

        $this->insertLeadCodLog();
        $this->phone = $this->go_request->getPhoneParam();
        if (empty($this->phone)) {
            throw new \LogicException('Не указан телефон для cod заказа. Request: ' . serialize($this->request->all()));
        }

        $this->name = $this->go_request->getClientParam();
        $this->address = $this->go_request->getAddress();
        $this->email = $this->go_request->getEmailParam();
        $this->contact_type = $this->go_request->getContactType();
        $this->products = $this->go_request->getProductsParam();
        $this->custom = $this->go_request->getCustomParam();

        $flow = $this->data_container->getFlow();
        $visitor = $this->data_container->getVisitor();

        $target_geo_hash = $this->go_request->getTargetGeoHash();
        $target_geo = $this->target_geo->getByHash(
            $target_geo_hash,
            ['country'],
            (int)$flow['publisher_id']
        );
        $this->phone_info = $this->phone_inspector->checkValid($this->phone, $target_geo['country']['code']);

        $double_lead_hash = LeadDoubleValidator::getLeadForParams(
            $this->phone_info['after_processing'],
            $target_geo_hash
        );

        if (!\is_null($double_lead_hash)) {
            $redirect = $this->go_request->getSuccessRedirect($double_lead_hash);

        } else {
            $order = $this->insertOrder();
            $lead = $this->insertLead($order, $target_geo);

            TempLead::closeBySID($visitor['s_id']);

            if (!$this->phone_info['is_valid']) {
                $redirect = $this->go_request->getCorrectRedirect($lead['hash']);
            } else {
                $redirect = $this->go_request->getSuccessRedirect($lead['hash']);
            }
        }

        return $redirect;
    }

    public function insertOrder(): Order
    {
        return $this->order->createNew([
            'name' => $this->name,
            'phone' => $this->phone_info['after_processing'],
            'address' => $this->address,
            'comment' => mb_substr($this->request->input('comment', ''), 0, 255),
            'email' => $this->email,
            'contact_type' => $this->contact_type,
            'products' => $this->products,
            'custom' => $this->custom,
            'number_type_id' => $this->phone_info['number_type'],
            'history' => json_encode([[
                'date' => time(),
                'name' => $this->name,
                'origin_phone' => $this->phone_info['origin'],
                'phone' => $this->phone_info['after_processing'],
            ]])
        ]);
    }

    public function insertLead(Order $order_info, TargetGeo $target_geo): Lead
    {
        $visitor = $this->data_container->getVisitor();
        $landing = $this->data_container->getLanding();
        $flow = $this->data_container->getFlow();

        $device_info = $this->device_inspector->getDeviceIdentifiers($visitor['user_agent']);

        return $this->lead->createNew([
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
            'flow_id' => $flow['is_virtual'] ? 0 : $flow['id'],
            'order_id' => $order_info['id'],
            'payout' => $target_geo['payout'],
            'price' => $target_geo['price'],
            'currency_id' => $target_geo['payout_currency_id'],
            'origin' => Lead::WEB_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'initialized_at' => $this->data_container->getFlowClickDate()->toDateTimeString(),
            'is_valid_phone' => $this->phone_info['is_valid'],
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
            'is_back_call' => $this->go_request->getIsBackcall(),
            'is_back_action' => $this->go_request->getIsBackaction(),
        ]);
    }

    private function insertLeadCodLog(): void
    {
        $log =
            "-----\n"
            . 'Дата:' . date('Y-m-d H:i:s')
            . ';Имя:' . $this->request->get('client')
            . ';Тел:' . $this->request->get('phone')
            . ';Prod:' . $this->request->get('products', '-')
            . ';Target:' . $this->request->get('target_geo_hash', '-')
            . ';Subdomain:' . ($_SERVER['SUBDOMAIN'] ?? '')
            . "\n";

        \File::append(storage_path() . '/logs/leads.log', $log);
    }
}
