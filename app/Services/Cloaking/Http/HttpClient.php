<?php
declare(strict_types=1);

namespace App\Services\Cloaking\Http;

class HttpClient
{
    private $_userAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0';
    private $_info = array();
    private $_infoMime;

    public function getInfo()
    {
        return $this->_info;
    }

    public function userAgent($agent)
    {
        $this->_userAgent = $agent;
        return $this;
    }

    public function getMime($url = null)
    {
        if ($url) {
            $ch = $this->_curlInit($url);
            curl_exec($ch);
            $this->_infoMime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        }
        $charset = explode(';', $this->_infoMime);
        return (isset($charset[0])) ? $charset[0] : 'html/text';
    }

    public function get($url)
    {
        $ch = $this->_curlInit($url);
        $return = curl_exec($ch);
        $this->_info = curl_getinfo($ch);
        $charset = explode(';', $this->_info['content_type']);
        $this->_infoMime = $charset[0];
        curl_close($ch);
        return $return;
    }

    public function post($url, Array $params = null)
    {
        $ch = $this->_curlInit($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }

    private function _curlInit($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        @curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        return $ch;
    }
}