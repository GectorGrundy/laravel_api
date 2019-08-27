<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\GoDataContainer;
use App\Models\Landing;
use App\Models\Visitor;
use App\Services\GoUtmParameters;
use App\Services\VisitorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Заготовка для возможного рефакторинга получения параметров запроса с кэша визитора и текущего request
 */
class RequestParameters
{
    private $data_container;
    /**
     * @var VisitorService
     */
    private $visitor_service;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var GoUtmParameters
     */
    private $utm;

    public function __construct(GoDataContainer $data_container, VisitorService $visitor_service, GoUtmParameters $go_utm_parameters)
    {
        $this->data_container = $data_container;
        $this->visitor_service = $visitor_service;
        $this->utm = $go_utm_parameters;
    }

    public function handle(Request $request, \Closure $next)
    {
        $this->request = $request;

        $flow = $this->data_container->getFlow();
        $current_domain = $this->data_container->getCurrentDomain();

        // Its donor page of new cloaking
        if ($current_domain->isCloaked() && is_null($flow)) {
            return $next($request);
        }

        $site = $this->data_container->getSite();
        $visitor = $this->data_container->getVisitor();

        $tds_parameters = (new Visitor())->getFlowParameters($visitor['info'], $flow['offer_id'], $flow['hash']);

        $this->data_container->setData1(mb_substr($this->utm->getData1($tds_parameters), 0, 32));
        $this->data_container->setData2(mb_substr($this->utm->getData2($tds_parameters), 0, 32));
        $this->data_container->setData3(mb_substr($this->utm->getData3($tds_parameters), 0, 32));
        $this->data_container->setData4(mb_substr($this->utm->getData4($tds_parameters), 0, 32));
        $this->data_container->setClickid(mb_substr(request('clickid', $tds_parameters['clickid']), 0, 4096));

        $this->data_container->setFrom(mb_substr(request('from', ''), 0, 32));

        $from_transit_id = 0;
        $flow_click_date = Carbon::now();
        $from_transit_traffic_type = '';

        if (!$visitor['is_fallback']) {

            $flow_date_show = $this->visitor_service->getFlowDateShow($visitor['info'], $flow['offer_id'], $flow['hash']);

            if (!empty($flow_date_show)) {
                $flow_click_date = Carbon::createFromTimestamp($flow_date_show);
            }

            if ($site instanceof Landing) {
                $from_transit_id = $this->visitor_service->getLastFlowTransit($flow, $visitor['info']);

                // Если посетитель перехел на лендинг с прелендинга - получаем тип перехода
                if ($from_transit_id) {
                    $from_transit_traffic_type = $this->visitor_service->getTransitTrafficType([
                        'visitor_info' => $visitor['info'],
                        'offer_id' => $flow['offer_id'],
                        'flow_hash' => $flow['hash'],
                        'transit_id' => $from_transit_id,
                        'landing_id' => $site['id'],
                    ]);
                }
            }
        }

        $this->data_container->setFromTransitId($from_transit_id);
        $this->data_container->setFlowClickDate($flow_click_date);
        $this->data_container->setFromTransitTrafficType($from_transit_traffic_type);
        $this->data_container->setIsExtraFlow($this->visitor_service->getIsExtraFlow($visitor['info'], $flow));

        return $next($request);
    }
}
