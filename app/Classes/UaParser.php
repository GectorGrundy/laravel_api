<?php
declare(strict_types=1);

namespace App\Classes;

use UAParser\Parser;
use App\Exceptions\Custom\IncorrectUaException;

class UaParser
{
	private $ua;
	private $parsed_data;

	public function __construct(string $user_agent)
	{
		if (empty($user_agent)) {
			throw new IncorrectUaException('Not defined ua');
		}

		$this->ua = $user_agent;

		$this->parsed_data = $this->parse();
	}

	/**
	 * Парсинг юзер агента
	 *
	 * @return mixed
	 */
	public function parse()
	{
		$ua = $this->ua;

		$parser = Parser::create();

		return $parser->parse($ua);
	}

	/**
	 * Получение платформы
	 *
	 * @return mixed
	 */
	public function getOsPlatform()
	{
		return $this->parsed_data->os->family;
	}

	/**
	 * Получение основной версии операционной системы
	 *
	 * @return mixed
	 */
	public function getOsMajorVersion()
	{
		return $this->parsed_data->os->major;
	}

	/**
	 * Получение минорной версии операционной системы
	 *
	 * @return mixed
	 */
	public function getOsMinorVersion()
	{
		return $this->parsed_data->os->minor;
	}

	/**
	 * Получение патча версии операционной системы
	 *
	 * @return mixed
	 */
	public function getOsPatchVersion()
	{
		return $this->parsed_data->os->patch;
	}

	/**
	 * Получение браузера
	 *
	 * @return mixed
	 */
	public function getBrowser()
	{
		return $this->parsed_data->ua->family;
	}

	/**
	 * Получение основной версии браузера
	 *
	 * @return mixed
	 */
	public function getBrowserMajorVersion()
	{
		return $this->parsed_data->ua->major;
	}

	/**
	 * Получение девайса
	 *
	 * @return mixed
	 */
	public function getDevice()
	{
		return $this->parsed_data->device->family;
	}

	/**
	 * Получение типа устройства
	 *
	 * @return string
	 */
	public function getDeviceType()
	{
		$platform = $this->getDevice();

		switch ($platform) {
			case 'other':
				return 'PC';

			default:
				return 'Unknown';
		}
	}
}