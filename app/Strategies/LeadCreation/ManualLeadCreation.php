<?php
declare(strict_types=1);

namespace App\Strategies\LeadCreation;

use App\Classes\PhoneInspector;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\{
    Flow, Lead, Order, TargetGeo
};

class ManualLeadCreation
{
    use DispatchesJobs;

    private $phone_inspector;
    private $phone_info;

    public function __construct(PhoneInspector $phone_inspector)
    {
        $this->phone_inspector = $phone_inspector;
    }

    public function handle(int $flow_id, int $target_geo_id, string $name, string $phone)
    {
        $target_geo = (new TargetGeo())->getById($target_geo_id);

        $this->phone_info = $this->phone_inspector->checkValid($phone, $target_geo->country['code']);

        $order = $this->insertOrder($name);
        return $this->insertLead($order, $target_geo, $flow_id);
    }

    public function insertOrder(string $name)
    {
        return (new Order())->createNew([
            'name' => $name,
            'phone' => $this->phone_info['after_processing'],
            'number_type_id' => $this->phone_info['number_type'],
            'history' => json_encode([[
                'date' => time(),
                'name' => $name,
                'origin_phone' => $this->phone_info['origin'],
                'phone' => $this->phone_info['after_processing'],
            ]])
        ]);
    }

    public function insertLead(Order $order, TargetGeo $target_geo, int $flow_id): Lead
    {
        $flow = (new Flow())->getById($flow_id);

        return (new Lead())->createNew([
            'offer_id' => $flow['offer_id'],
            'target_id' => $flow['target_id'],
            'target_geo_id' => $target_geo['id'],
            'country_id' => $target_geo['country_id'],
            'publisher_id' => $flow['publisher_id'],
            'flow_id' => $flow['id'],
            'order_id' => $order['id'],
            'payout' => $target_geo['payout'],
            'price' => $target_geo['price'],
            'currency_id' => $target_geo['payout_currency_id'],
            'origin' => Lead::API_ORIGIN,
            'type' => Lead::COD_TYPE,
            'status' => Lead::NEW,
            'is_valid_phone' => $this->phone_info['is_valid'],
            'initialized_at' => Carbon::now()->toDateTimeString()
        ]);
    }
}
