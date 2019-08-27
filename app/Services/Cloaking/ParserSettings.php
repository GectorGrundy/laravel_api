<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class ParserSettings
{
    private static $current_domain;
    private static $donor_url;
    private static $donor_charset;
    private static $replacements;

    /**
     * @return mixed
     */
    public static function getDonorCharset()
    {
        return self::$donor_charset;
    }

    /**
     * @param mixed $donor_charset
     */
    public static function setDonorCharset($donor_charset)
    {
        self::$donor_charset = $donor_charset;
    }

    public static function getDonorUrl(): string
    {
        return self::$donor_url;
    }

    public static function setDonorUrl($donor_url)
    {
        self::$donor_url = $donor_url;
    }

    public static function getCurrentDomain()
    {
        return self::$current_domain;
    }

    public static function setCurrentDomain($current_domain)
    {
        self::$current_domain = $current_domain;
    }

    public static function setReplacements(array $replacements): void
    {
        self::$replacements = $replacements;
    }

    public static function getReplacements(): array
    {
        return self::$replacements;
    }

    public static function get($key)
    {
        // http://www.iatronet.gr
        // https://www.elconfidencial.com
        // http://www.sportlife.es
        // https://elpais.com
        // http://www.donnamoderna.com
        // https://linfavitale.com

        switch ($key) {
            case 'synsDictonary':
                return false;

            case 'synonimize':
                return false;

            case 'translateSource':
                return false;

            case 'translateTarget':
                return false;

            case 'synonymsOrder':
                return false;

            case 'http_cookies_write':
                return 'http_cookies_write';

            case 'http_cookies_read':
                return 'http_cookies_read';

            case 'cacheLimitType':
                return 'cacheOnly';

            case 'replaces':
                return '{}';

            case 'notCacheUrls':
                return '{}';

            case 'sites_path':
                return storage_path('app/sites');

            case 'translateAdapter':
                return NotTranslateAdapter::class;

            case 'otherCss':
                return true;

            case 'otherImg':
                return true;

            case 'img_min_w':
                return 0;

            case 'img_min_h':
                return 0;

            case 'reflection':
                return false;

            case 'enable_copyright':
                return false;

            case 'logoPos':
                return false;

            case 'logoFont':
                return false;

            case 'logo':
                return false;

            case 'logo_collor':
                return false;

            default:
                dd('Settings::staticGet', $key);
        }
    }
}
