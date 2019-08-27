<?php
declare(strict_types=1);

namespace App\Classes;

/**
 * Class IpInspector
 * @package App\Classes
 */
class IpInspector
{

	/**
	 * Получение ip адресса
	 *
	 * @return mixed
	 */
    public function getIp()
    {
        if ($this->issetXForwarderForIp() && $this->checkUserIpFromXForwarderFor()) {
            return $this->getXForwarderForIp();
        }

        if ($this->issetClientIp()) {
            return $this->getClientIp();
        }

        return $this->getRemoteAddrIp();
    }

	/**
	 * Получение списка ip адрессов со всех заголовков
	 *
	 * @return array
	 */
	public function getIps()
	{
		$ips = [];

		$ips['REMOTE_ADDR'] = $this->getRemoteAddrIp();

        if ($this->issetXForwarderForIp()) {
            $ips['HTTP_X_FORWARDED_FOR'] = $this->getXForwarderForIps();
        }

        if ($this->issetClientIp()) {
            $ips['HTTP_CLIENTIP'] = $this->getClientIp();
        }

		return $ips;
	}

	/**
	 * Проверка на существование заголовка HTTP_X_FORWARDED_FOR и что он не пустой
	 *
	 * @return bool
	 */
	public function issetXForwarderForIp()
	{
		return isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']);
	}

	/**
	 * Получение ip с заголовка HTTP_X_FORWARDED_FOR
	 *
	 * @return mixed
	 */
	public function getXForwarderForIp()
	{
		if ($this->xForwardedForHasSeveralIps()) {
			return $this->getUserIpFromXForwarderFor();
		}

		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	/**
	 * Проверка на существование нескольких ip адресов в заголовке HTTP_X_FORWARDED_FOR
	 *
	 * @return bool
	 */
	private function xForwardedForHasSeveralIps()
	{
		return count($this->getXForwarderForIps()) > 0;
	}

	/**
	 * Проверка ip пользователя с заголовка HTTP_X_FORWARDED_FOR на корректность
	 *
	 * @return bool
	 */
	public function checkUserIpFromXForwarderFor(): bool
	{
		return ip2long($this->getUserIpFromXForwarderFor()) !== false;
	}

	/**
	 * Получение ip адреса пользователя с заголовка HTTP_X_FORWARDED_FOR если тот имеет цепочку ip адресов
	 *
	 * @return mixed
	 */
	private function getUserIpFromXForwarderFor()
	{
		$ips = $this->getXForwarderForIps();
		$user_ip = end($ips);

		return $user_ip;
	}


	/**
	 * Получение массива ip адресов с заголовка HTTP_X_FORWARDED_FOR
	 *
	 * @return array
	 */
	private function getXForwarderForIps()
	{
		return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	}

	/**
	 * Проверка на существование заголовка HTTP_CLIENTIP и что он не пустой. Передается в браузере UCWeb
	 *
	 * @return bool
	 */
	public function issetClientIp()
	{
		return isset($_SERVER['HTTP_CLIENTIP']) && $_SERVER['HTTP_CLIENTIP'] != '';
	}

	/**
	 * Получение ip с заголовка HTTP_CLIENTIP
	 *
	 * @return mixed
	 */
	public function getClientIp()
	{
		return $_SERVER['HTTP_CLIENTIP'];
	}

	/**
	 * Получение ip с заголовка REMOTE_ADDR.
	 *
	 * @return mixed
	 */
	public function getRemoteAddrIp()
	{
		return $_SERVER['REMOTE_ADDR'] ?? null;
	}
}