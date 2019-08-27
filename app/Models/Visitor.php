<?php
declare(strict_types=1);

namespace App\Models;

use Cache;
use Hashids;
use ErrorException;
use App\Models\Traits\EloquentVisitorHash;
use App\Exceptions\Hashids\NotDecodedHashException;
use App\Exceptions\Visitor\IncorrectCacheDataException;

class Visitor extends AbstractEntity
{
    use EloquentVisitorHash;

    protected $fillable = ['session_id', 'data'];

    public static $session_ids_cache_key = 'visitor:session_ids_cache_key';

    /**
     * Получение данных о клиенте по его session_id
     *
     * @param string $session_id
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws NotDecodedHashException
     */
    public function getInfoBySessionId(string $session_id): array
    {
        $key = "visitor:getInfoBySessionId:{$session_id}";

        $visitor_data = Cache::get($key, function () use ($session_id) {

            try {
                $decoded_data = Hashids::connection('visitor')->decode($session_id);
                if (count($decoded_data) < 1) {
                    throw new NotDecodedHashException('Failed to decode visitor id');
                }
            } catch (ErrorException $e) {
                throw new NotDecodedHashException('Failed to decode visitor id');
            }


            $visitor_id = $decoded_data[0];

            $visitor_info = Visitor::findOrFail($visitor_id);

            return $visitor_info->data;
        });

        return json_decode($visitor_data, TRUE);
    }

    public function createNew(array $data): array
    {
        return self::create(['data' => json_encode($data)])->toArray();
    }

    /**
     * Запись данных о клиенте
     *
     * @param string $s_id
     * @param array $visitor_info
     */
    public function setInfoBySessionId(string $s_id, array $visitor_info)
    {
        $key = "visitor:getInfoBySessionId:{$s_id}";

        $visitor_info = json_encode($visitor_info);

        Cache::put($key, $visitor_info, config('env.visitor_cache_lifetime'));

        $this->addSidToSyncQueue($s_id);
    }

    /**
     * Кэширование session_id для возможности дальнейшей синхронизации данных кэша и БД
     *
     * @param $key
     */
    private function addSidToSyncQueue($key)
    {
        \Redis::connection('system')->sadd(self::$session_ids_cache_key, $key);
    }

    /**
     * Поиск ID прелендинга для нужного оффера и потока в кэше посетителя
     *
     * @param array $visitor
     * @param Flow $flow
     * @return int
     */
    public function getViewedTransitIdForFlow(array $visitor, Flow $flow): int
    {
        // Если он не переходил на текущий оффер
        if (!isset($visitor['viewed_offers'][$flow['offer_id']])) {
            return 0;
        }

        // Если он не переходил на текущий оффер потока
        if (!isset($visitor['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']])) {
            return 0;
        }

        // Если для текущего оффера потока нету данных по прелендингу
        if (!isset($visitor['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']]['transits']['last_showed'])) {
            return 0;
        }

        $transit_id = (int)$visitor['viewed_offers'][$flow['offer_id']]['flows'][$flow['hash']]['transits']['last_showed'];

        return $transit_id;
    }

    /**
     * Поиск ID лендинга для нужного оффера и потока в кэше посетителя
     *
     * @param array $visitor_info
     * @param int $offer_id
     * @param string $flow_hash
     * @return int
     */
    public function getViewedLandingIdForFlow(array $visitor_info, int $offer_id, string $flow_hash): int
    {
        // Если он не переходил на текущий оффер
        if (!isset($visitor_info['viewed_offers'][$offer_id])) {
            return 0;
        }

        // Если он не переходил на текущий оффер потока
        if (!isset($visitor_info['viewed_offers'][$offer_id]['flows'][$flow_hash])) {
            return 0;
        }

        // Если для текущего оффера потока нету данных по лендингу
        if (!isset($visitor_info['viewed_offers'][$offer_id]['flows'][$flow_hash]['landings']['last_showed'])) {
            return 0;
        }

        $landing_id = (int)$visitor_info['viewed_offers'][$offer_id]['flows'][$flow_hash]['landings']['last_showed'];

        return $landing_id;
    }

    /**
     * Получение идентификатора базы данных где хранятся данные посетителя
     *
     * @param $hash
     * @return int
     * @throws NotDecodedHashException
     */
    public function parseDatabaseIdFromHash($hash)
    {
        $decoded_data = Hashids::connection('visitor')->decode($hash);
        if (count($decoded_data) < 1) {
            throw new NotDecodedHashException('Failed to decode hash');
        }

        if (!isset($decoded_data[1])) {
            throw new NotDecodedHashException('Failed to decode hash');
        }

        return $decoded_data[1];
    }

    /**
     * Получение идентификатора потока, через который посетитель последний раз был в указанного оффера
     *
     * @param $visitor_info
     * @param $offer_id
     * @return mixed
     * @throws IncorrectCacheDataException
     */
    public function getLastShowedFlowHashInOffer($visitor_info, $offer_id)
    {
        if (!isset($visitor_info['viewed_offers'][$offer_id]['last_showed_flow_hash'])) {
            throw new IncorrectCacheDataException();
        }

        return $visitor_info['viewed_offers'][$offer_id]['last_showed_flow_hash'];
    }


    /**
     * Получение параметров URL посетителя, с которыми он переходил по ссылке потока
     *
     * @param $visitor_info
     * @param $offer_id
     * @param $flow_hash
     * @return mixed
     */
    public function getFlowParameters($visitor_info, $offer_id, $flow_hash)
    {
        return [
            'data1' => $this->getFlowTdsParameter('data1', $visitor_info, $offer_id, $flow_hash),
            'data2' => $this->getFlowTdsParameter('data2', $visitor_info, $offer_id, $flow_hash),
            'data3' => $this->getFlowTdsParameter('data3', $visitor_info, $offer_id, $flow_hash),
            'data4' => $this->getFlowTdsParameter('data4', $visitor_info, $offer_id, $flow_hash),
            'clickid' => $this->getFlowTdsParameter('clickid', $visitor_info, $offer_id, $flow_hash),
            'cpc' => $this->getFlowTdsParameter('cpc', $visitor_info, $offer_id, $flow_hash),
            'referer' => $this->getFlowTdsParameter('referer', $visitor_info, $offer_id, $flow_hash),
        ];
    }

    /**
     * Получение параметра потока, с которым пользователь пришел по ссылке потока
     *
     * @param string $parameter
     * @param array $visitor
     * @param int $offer_id
     * @param string $flow_hash
     * @return string
     */
    private function getFlowTdsParameter(string $parameter, array $visitor, int $offer_id, string $flow_hash)
    {
        return $visitor['viewed_offers'][$offer_id]['flows'][$flow_hash]['data_parameters'][$parameter] ?? '';
    }
}
