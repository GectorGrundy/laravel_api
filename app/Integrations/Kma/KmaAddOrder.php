<?php
declare(strict_types=1);

namespace App\Integrations\Kma;

use App\Exceptions\Integration\FailAuth;
use App\Integrations\ReleaseJob;
use App\Models\{
    DeviceType, Integration, Lead, TargetGeo
};
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class KmaAddOrder implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;
    use ReleaseJob;

    public const API_URL = 'http://api.kma1.biz/';

    public const DUBL_CODE = 8;
    /**
     * @var Lead
     */
    private $lead;
    /**
     * @var Integration
     */
    private $integration;
    /**
     * @var int
     */
    private $lead_id;
    /**
     * @var int
     */
    private $integration_id;
    /**
     * @var string
     */
    private $authid;
    /**
     * @var string
     */
    private $authhash;


    public function __construct(int $lead_id, int $integration_id)
    {
        $this->lead_id = $lead_id;
        $this->integration_id = $integration_id;
    }

    public function handle(): void
    {
        $this->lead = Lead::findOrFail($this->lead_id);
        if ($this->lead->cannotIntegrate()) {
            return;
        }

        $this->integration = Integration::findOrFail($this->integration_id);

        try {
            $this->auth();
        } catch (FailAuth $e) {
            $this->releaseJob();
            return;
        }

        $response = $this->sendLead();
        $code = (int)$response['code'];

        if ($code !== 0) {
            if ($code === self::DUBL_CODE) {
                $this->lead->setAsIntegrated($this->lead->generateExternalKeyById());
                $this->lead->trash(Lead::DUBL_SUBSTATUS_ID);
            } else {
                $this->releaseJob();
                throw new \LogicException('Пришел неизвестный статус от KMA. Response: ' . serialize($response));
            }
            return;
        }

        $this->lead->setAsIntegrated($response['orderid']);
    }

    private function auth(): void
    {
        $url = sprintf(self::API_URL . '?method=auth&username=%s&pass=%s',
            $this->integration['integration_data_array']['username'],
            $this->integration['integration_data_array']['password']
        );

        $result = sendIntegrationRequest($url);
        $response = json_decode($result['response'], true);

        if ($this->httpCodeBad($result['curl_info']['http_code']) || (int)$response['code'] !== 0) {
            throw new FailAuth('Kma', $response);
        }

        $this->authid = $response['authid'];
        $this->authhash = $response['authhash'];
    }

    private function sendLead(): array
    {
        $params = array_merge([
            'method' => 'addlead',
            'authid' => $this->authid,
            'authhash' => $this->authhash,
            'name' => $this->lead->order['name'],
            'phone' => $this->lead->order['phone'],
            'ip' => $this->getIp(),
            'ismobile' => in_array((int)$this->lead['device_type_id'], [DeviceType::MOBILE, DeviceType::TABLET]) ? 1 : 0,
            'data1' => $this->lead['hash']
        ],
            $this->lead->target_geo_rule['integration_data_array']
        );

        $result = sendIntegrationRequest(self::API_URL, 'POST', $params);

        $response = json_decode($result['response'], true);

        $this->insertLog($params, array_merge($response, ['http_code' => $result['curl_info']['http_code']]));

        return $response;
    }

    private function getIp()
    {
        return empty($this->lead['ip'])
            ? $this->getIpByTargetGeo($this->lead->target_geo)
            : $this->lead['ip'];
    }

    private function getIpByTargetGeo(TargetGeo $target_geo)
    {
        return Lead::where('target_geo_id', $target_geo['id'])
            ->where('ip', '!=', '')
            ->limit(10)
            ->get()
            ->random()['ip'];
    }

    private function httpCodeBad($http_code): bool
    {
        return $http_code !== 200;
    }

    private function insertLog(array $params, array $response)
    {
        $log = "-----\n"
            . "Method:addlead" . "\n"
            . 'Date:' . date('d.m.Y H:i:s') . "\n"
            . 'Url:' . self::API_URL . http_build_query($params) . "\n"
            . 'Response:' . serialize($response)
            . "\n";

        \File::append(storage_path('logs/kma.log'), $log);
    }
}
