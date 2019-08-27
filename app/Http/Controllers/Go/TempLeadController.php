<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Classes\PhoneInspector;
use App\Http\Controllers\Controller;
use App\Http\GoDataContainer;
use App\Http\Requests\Go\GoRequest;
use App\Models\TargetGeo;
use App\Models\TempLead;
use App\Services\TempLeadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TempLeadController extends Controller
{
    private $request;
    private $go_request;
    private $data_container;

    public function __construct(Request $request, GoRequest $go_request, GoDataContainer $data_container)
    {
        $this->request = $request;
        $this->go_request = $go_request;
        $this->data_container = $data_container;
    }

    public function create(PhoneInspector $phone_inspector)
    {
        $this->validate($this->request, [
            'target_geo_hash' => 'required|string',
            'phone' => 'required|string',
            'name' => 'string',
        ]);

        try {
            $target_geo = (new TargetGeo())->getByHash($this->request->input('target_geo_hash'), [
                'country', 'target.offer'
            ]);
        } catch (ModelNotFoundException $e){
            return;
        }

        if (!$target_geo->target->offer['is_temp_lead']) {
            return;
        }

        $phone_validation = $phone_inspector->checkValid($this->request->input('phone'), $target_geo->country->code);

        if (!$phone_validation['is_valid']) {
            return;
        }
        $phone = $phone_validation['after_processing'];

        $current_domain = $this->data_container->getCurrentDomain();
        $visitor = $this->data_container->getVisitor();
        $landing = $this->data_container->getLanding();
        $flow = $this->data_container->getFlow();

        $is_double = TempLeadService::isDouble($flow, $landing, $phone, $visitor['s_id']);
        if ($is_double) {
            return;
        }

        TempLead::create([
            'target_geo_id' => $target_geo->id,
            'flow_id' => $flow->id,
            'transit_id' => $this->data_container->getFromTransitId(),
            'landing_id' => $landing->id,
            'domain_id' => $current_domain['id'],
            'name' => $this->go_request->getClientParam(),
            'phone' => $phone,
            'products' => $this->go_request->getProductsParam(),
            'comment' => $this->request->input('comment', ''),
            'ip' => $visitor['ip'],
            'ips' => json_encode($visitor['ips']),
            'data1' => $this->data_container->getData1(),
            'data2' => $this->data_container->getData2(),
            'data3' => $this->data_container->getData3(),
            'data4' =>$this->data_container->getData4(),
            'clickid' => $this->data_container->getClickid(),
            's_id' => $visitor['s_id'],
            'user_agent' => $visitor['user_agent'],
            'referer' => $visitor['referer'],
            'transit_traffic_type' => $this->data_container->getFromTransitTrafficType(),
            'initialized_at' => $this->data_container->getFlowClickDate()->toDateTimeString(),
            'region_id' => $visitor['geo_ids']['region_id'],
            'city_id' => $visitor['geo_ids']['city_id'],
            'ip_country_id' => $visitor['geo_ids']['country_id'],
            'is_extra_flow' => $this->data_container->isExtraFlow(),
            'browser_locale' => $visitor['browser_locale'],
        ]);
    }
}
