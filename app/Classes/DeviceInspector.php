<?php
declare(strict_types=1);

namespace App\Classes;

use Detection\MobileDetect;
use App\Models\{
    Browser, DeviceType, OsPlatform
};
use App\Exceptions\Custom\IncorrectUaException;

class DeviceInspector
{
    private $ua;
    /**
     * @var MobileDetect
     */
    private $mobile_detect;
    /**
     * @var UaParser
     */
    private $ua_parser;
    private $device_type;
    private $os_platform;
    private $browser;

    public function __construct(DeviceType $device_type, OsPlatform $os_platform, Browser $browser)
    {
        $this->device_type = $device_type;
        $this->os_platform = $os_platform;
        $this->browser = $browser;
    }

    /**
     * Получение идентификаторов девайса
     *
     * @param $ua
     * @return array
     */
    public function getDeviceIdentifiers($ua): array
    {
        $default = [
            'device_type_id' => 0,
            'os_platform_id' => 0,
            'browser_id' => 0,
        ];

        if (empty($ua)) {
            return $default;
        }

        try {
            $device_info = $this->getDeviceInfo($ua);
        } catch (IncorrectUaException $e) {
            return $default;
        }

        $data = [];

        $data['device_type_id'] = $this->device_type->getInfoByTitle($device_info['device_type'])['id'];

        // Получаем индентификатор ОС
        $data['os_platform_id'] = 0;
        if (!\is_null($device_info['os_platform'])) {
            $data['os_platform_id'] = $this->os_platform->getInfoByTitleOrCreate($device_info['os_platform'])['id'];
        }

        // Получаем индентификатор браузера
        $data['browser_id'] = 0;
        if (!\is_null($device_info['browser'])) {
            $data['browser_id'] = $this->browser->getInfoByTitleOrCreate($device_info['browser'])['id'];
        }

        return $data;
    }

    private function parse($ua)
    {
        $this->ua = $ua;
        $this->ua_parser = new UaParser($ua);
        $this->mobile_detect = new MobileDetect([], $ua);
    }

    /**
     * Получение полной информации об устройстве
     *
     * @param string $ua
     * @return array
     */
    public function getDeviceInfo(string $ua): array
    {
        $this->parse($ua);

        return [
            'device_type' => $this->getDeviceType(),
            'os_platform' => trim($this->getOsPlatform() . ' ' . $this->getOsVersion()),
            'browser' => trim($this->getBrowser() . ' ' . $this->getBrowserVersion()),
        ];
    }

    /**
     * Получение типа устройства.
     * Пока реализовано только определение таблета и моб. телефона, а все остальные идут как Desktop
     *
     * @return string
     */
    public function getDeviceType(): string
    {
        if ($this->mobile_detect->isTablet()) {
            return 'Tablet';
        }

        if ($this->mobile_detect->isMobile()) {
            return 'Mobile phone';
        }

        return 'Desktop';
    }

    /**
     * Получение названия платформы.
     *
     * @return mixed|string
     */
    public function getOsPlatform()
    {
        $os_platform = $this->ua_parser->getOsPlatform();

        // С названием ОС Windows идет также ее версия
        if (strpos($os_platform, 'Windows') !== false) {
            return 'Windows';
        }

        return $os_platform;
    }

    /**
     * Получение версии операционной системы
     *
     * @return string
     */
    public function getOsVersion(): string
    {
        $os_platform = $this->ua_parser->getOsPlatform();

        if (strpos($os_platform, 'Windows') !== false) {

            if (strpos($os_platform, ' ') !== false) {
                $os_version = explode(' ', $os_platform)[1];
            }

        } else if (strtolower($os_platform) === 'android') {

            $os_version = $this->ua_parser->getOsMajorVersion();

            if ($this->ua_parser->getOsMinorVersion()) {
                $os_version .= "." . $this->ua_parser->getOsMinorVersion();
            }

            if ($this->ua_parser->getOsPatchVersion()) {
                $os_version .= "." . $this->ua_parser->getOsPatchVersion();
            }
        }

        return $os_version ?? '';
    }

    /**
     * Получение браузера
     *
     * @return mixed
     */
    public function getBrowser()
    {
        return $this->ua_parser->getBrowser();
    }

    /**
     * Получение версии браузера
     *
     * @return mixed
     */
    public function getBrowserVersion()
    {
        return $this->ua_parser->getBrowserMajorVersion();
    }

    public function isFacebookBrowser(string $ua): bool
    {
        return (bool)preg_match('~FBAN|FBIOS|FBDV|FBSN|FB_IAB~', $ua);
    }

    public function isInstagramBrowser(string $ua): bool
    {
        return (bool)preg_match('~Instagram~', $ua);
    }
}
