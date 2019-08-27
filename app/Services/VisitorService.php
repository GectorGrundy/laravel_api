<?php
declare(strict_types=1);

namespace App\Services;

use App\Http\GoDataContainer;
use App\Models\Flow;
use App\Models\Lead;
use App\Models\Transit;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VisitorService
{
    /**
     * Получение даты последнего перехода по ссылке потока посетителем
     *
     * @param array $visitor_info
     * @param int $offer_id
     * @param string $flow_hash
     * @return int
     */
    public function getFlowDateShow(array $visitor_info, int $offer_id, string $flow_hash): int
    {
        if (!isset($visitor_info['viewed_offers'][$offer_id]['flows'][$flow_hash]['date_show'])) {
            return 0;
        }

        return $visitor_info['viewed_offers'][$offer_id]['flows'][$flow_hash]['date_show'];
    }

    /**
     * Получение идентификатора прелендинга оффера потока, на которой пользователь последний раз был
     *
     * @param Flow $flow
     * @param $visitor_info
     * @return int
     */
    public function getLastFlowTransit(Flow $flow, $visitor_info): int
    {
        return (int)($visitor_info['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']]['transits']['last_showed'] ?? 0);
    }

    /**
     * Получение типа перехода посетителя с прелендинга на лендинг
     *
     * @param $params
     * @return string
     */
    public function getTransitTrafficType($params): string
    {
        $transit_traffic_type = '';
        if (isset($params['visitor_info']['viewed_offers'][$params['offer_id']]['flows'][$params['flow_hash']]['rel'][$params['transit_id'] . '-' . $params['landing_id']]['transit_traffic_type'])) {
            $transit_traffic_type = $params['visitor_info']['viewed_offers'][$params['offer_id']]['flows'][$params['flow_hash']]['rel'][$params['transit_id'] . '-' . $params['landing_id']]['transit_traffic_type'];
        }

        return $transit_traffic_type;
    }

    /**
     * Получение параметра is_extra_flow с кэша визитора
     *
     * @param $visitor_info
     * @param Flow $flow
     * @return bool
     */
    public function getIsExtraFlow($visitor_info, Flow $flow): bool
    {
        return (bool)($visitor_info['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']]['is_extra_flow'] ?? false);
    }

    public function setDataAfterSiteVisited(GoDataContainer $data_container): void
    {
        $visitor = $data_container->getVisitor();
        try {
            $visitor_data = (new Visitor())->getInfoBySessionId($visitor['s_id']);
        } catch (ModelNotFoundException $e) {
            $visitor_data = [];
        }

        $flow = $data_container->getFlow();

        // Если он не переходил на текущий оффер
        if (!isset($visitor_data['viewed_offers'][$flow['offer_id']])) {
            $visitor_data['viewed_offers'][$flow['offer_id']] = [];
        }

        // Запись последнего посещенного потока оффера
        $visitor_data['viewed_offers'][$flow['offer_id']]['last_showed_flow_hash'] = $flow['hash'];

        // Если нету данных по текущему потоку
        if (!isset($visitor_data['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']])){
            $visitor_data['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']] = [];
        }

        // Для большей удобочитаемости записуем данные по потоку в переменную
        // !ВНИМАНИЕ! Работа с указателем
        $flow_data = &$visitor_data['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']];

        // Запись даты потока
        $flow_data['date_show'] = time();

        // Пишем параметры перехода
        $flow_data['data_parameters'] = [
            'data1' => $data_container->getData1(),
            'data2' => $data_container->getData2(),
            'data3' => $data_container->getData3(),
            'data4' => $data_container->getData4(),
            'clickid' => $data_container->getClickid(),
        ];

        // Установка даты показа оффера
        $visitor_data['viewed_offers'][$flow['offer_id']]['date_show'] = time();

        $site = $data_container->getSite();

        if ($site instanceof Transit) {
            // Если есть данные по прелендингу и нету данных по нему в кэше
            if (!isset($flow_data['transits'][$site['id']])) {

                $flow_data['transits'][$site['id']] = [
                    'id' => $site['id'],
                    'count_show' => 0,
                    'date_show' => 0
                ];
            }
            // Запись идентификатора последней посещенного прелендинга
            $flow_data['transits']['last_showed'] = $site['id'];

            // Инкремент кол-ва показов прелендинга, установка даты перехода
            $flow_data['transits'][$site['id']]['count_show'] += 1;
            $flow_data['transits'][$site['id']]['date_show'] = time();

        } else {

            // Если нету данных по лендингу
            if (!isset($flow_data['landings'][$site['id']])) {
                $flow_data['landings'][$site['id']] = [
                    'id' => $site['id'],
                    'count_show' => 0,
                    'date_show' => 0
                ];
            }

            // Если переход на лендинг был с прелендинга - записуем тип перехода и дату перехода
            $from_transit_id = $data_container->getFromTransitId();
            if ($from_transit_id > 0) {

                if (!isset($flow_data['rel'])) {
                    $flow_data['rel'] = [];
                }

                $rel = $from_transit_id . '-' . $site['id'];

                $flow_data['rel'][$rel]['date_show'] = time();
                $flow_data['rel'][$rel]['transit_traffic_type'] = Lead::getTransitTrafficType($data_container->getFrom());
            }

            // Инкремент кол-ва показов лендинга, установка даты перехода
            $flow_data['landings'][$site['id']]['count_show'] += 1;
            $flow_data['landings'][$site['id']]['date_show'] = time();

            // Запись идентификатора последнего посещенного лендинга
            $flow_data['landings']['last_showed'] = $site['id'];
        }

        // Если в потока настроен редирект на другой поток после перехода с прелендинга на лендинг
        $flow_data['is_extra_flow'] = (int)request('is_extra_flow', 0);

        (new Visitor())->setInfoBySessionId($visitor['s_id'], $visitor_data);
    }
}
