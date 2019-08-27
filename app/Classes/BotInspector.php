<?php
declare(strict_types=1);

namespace App\Classes;

/**
 * Класс для проверки пользователя на принадлежность к боту по IP адрессу и user agent
 */
class BotInspector
{
    private $csv_parser;

    public function __construct(CsvParser $csv_parser)
    {
        $this->csv_parser = $csv_parser;
    }

    /**
     * Инициализирующий метод проверки пользователя на принадлежность к боту
     *
     * @param $user_agent
     * @param $ip
     * @param $server
     * @return bool
     */
    public function isBot(?string $user_agent, string $ip, array $server): bool
    {
        if (isset($server['HTTP_X_PURPOSE']) && $server['HTTP_X_PURPOSE'] === 'preview') {
            return true;
        }

        if (empty($user_agent)) {
            return true;
        }

        if ($this->checkIp($ip)) {
            return true;
        }

        return false;
    }

    /**
     * Проверка IP адресса
     *
     * @param $ip
     * @return bool
     */
    private function checkIp($ip)
    {
        if (is_null($ip) || $ip === '' || ip2long($ip) === false) {
            return true;
        }

        if ($this->checkIpInRange($ip)) {
            return true;
        }

        if ($this->checkIpByIsp($ip)) {
            return true;
        }

        return false;
    }

    /**
     * Проверка вхождения ip в диапазоны ip ботов
     *
     * @param $ip
     * @return bool
     */
    private function checkIpInRange($ip)
    {
        $bot_ips = $this->getBotIpRangesArray();

        foreach ($bot_ips AS $bot_ip_str) {

            $parsed_data = explode(" - ", $bot_ip_str);

            $ip_from = $parsed_data[0];
            $ip_to = $parsed_data[1];

            if (ip2long($ip) >= ip2long($ip_from) && ip2long($ip) <= ip2long($ip_to)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Получения списка ip-диапазонов ботов
     *
     * @return array
     */
    private function getBotIpRangesArray()
    {
        return $this->csv_parser->parseFile(config_path('bot_ip_ranges.csv'));
    }

    /**
     * Проверка принадлежности ip к интернет провайдерам ботов
     *
     * @param $ip
     * @return bool
     */
    private function checkIpByIsp($ip)
    {
        $bot_isp_list = $this->getBotIspArray();

        $ip_isp = getIpIsp($ip);
        if (!$ip_isp || in_array($ip_isp, $bot_isp_list)) {
            return true;
        }

        return false;
    }

    /**
     * Получения массива ISP ботов
     *
     * @return array
     */
    private function getBotIspArray()
    {
        return $this->csv_parser->parseFile(config_path('bot_isp_list.csv'));
    }

}