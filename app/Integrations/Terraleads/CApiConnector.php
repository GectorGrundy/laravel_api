<?php
declare(strict_types=1);

namespace App\Integrations\Terraleads;

use Exception;

class CApiConnector
{
    public $config = array(
        'api_key' => '9b90fd842369d6577499f2ed6d54a571',
        'offer_id' => 1502,
        'user_id' => 3798,
        'create_url' => 'http://tl-api.com/api/lead/create',
        'update_url' => 'http://tl-api.com/api/lead/update',
        'status_url' => 'http://tl-api.com/api/lead/status',
    );

    public function create($params)
    {
        $data = array(
            'offer_id' => $this->config['offer_id'],
            'user_id' => $this->config['user_id'],
            'name' => $params['name'],    //ФИО
            'phone' => $params['phone'],   //телефон
            'tz' => isset($params['tz']) ? $params['tz'] : '', //time zone
            'address' => isset($params['address']) ? $params['address'] : '', //address
            'country' => empty($params['country']) ? '' : $params['country'], //country
            //'stream_id'         => $params['stream_id'],    //stream
            //'utm_source'        => $params['utm_source'],    //utm marks
            //'utm_medium'        => $params['utm_medium'],
            //'utm_campaign'      => $params['utm_campaign'],
            //'utm_term'          => $params['utm_term'],
            //'utm_content'       => $params['utm_content'],
            'sub_id'            => $params['sub_id'],    //sub params
            //'sub_id_1'          => $params['sub_id_1'],
            //'sub_id_2'          => $params['sub_id_2'],
            //'sub_id_3'          => $params['sub_id_3'],
            //'sub_id_4'          => $params['sub_id_4']
        );

        $data['check_sum'] = sha1(
            $this->config['user_id'] .
            $this->config['offer_id'] .
            $data['name'] .
            $data['phone'] .
            $this->config['api_key']
        );

        $response = self::post_request($this->config['create_url'], json_encode($data));

        if ($response['http_code'] == 200 && $response['errno'] === 0) {
            $body = json_decode($response['result']);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $body;
            } else {
                throw new Exception('JSON response error');
            }
        } else {
            if (!empty($response['result'])) {
                $result = json_decode($response['result']);
                throw new Exception($result->error);
            } else {
                throw new Exception('HTTP request error. ' . $response['error']);
            }
        }
    }

    public function status($id)
    {
        $data = array(
            'id' => $id,
            'check_sum' => sha1($id . $this->config['api_key'])
        );

        $response = self::post_request($this->config['status_url'], json_encode($data));

        if ($response['http_code'] == 200 && $response['errno'] === 0) {
            $body = json_decode($response['result']);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $body;
            } else {
                throw new Exception('JSON response error');
            }
        } else {
            if (!empty($response['result'])) {
                $result = json_decode($response['result']);
                throw new Exception($result->error);
            } else {
                throw new Exception('HTTP request error. ' . $response['error']);
            }
        }
    }

    public static function post_request($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $response = array(
            'error' => $curl_error,
            'errno' => $curl_errno,
            'http_code' => $http_code,
            'result' => $result,
        );

        return $response;
    }
}
