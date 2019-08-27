<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use App\Models\Traits\StaticFileValidator;

class ParserEquals extends Parser
{
    use StaticFileValidator;

    public function __construct()
    {
    }

    public static function needAddHtmlToFileName($mime, $page)
    {
        $fileExt = substr($page, strrpos($page, '.') + 1);
        $isFile = in_array($fileExt, self::$extensions) ? true : false;
        return (self::needHtmlExtension($mime, $page) AND !$isFile);
    }

    public static function needHtmlExtension($mime, $page)
    {
        $isHtmlFile = ($mime === 'text/html');
        $pageStringHasNotExtension = (strpos($page, '.html') === false);
        return ($isHtmlFile AND $pageStringHasNotExtension);
    }

    public static function isRelativePath($path)
    {
        if (strpos($path, 'http:') === false AND strpos($path, 'https:') === false AND strpos($path, '//') === false) {
            return true;
        }
        return false;
    }

    public static function isNotIgnoredFiles($page)
    {
        foreach (self::$extensions as $file) {
            if (strpos($page, ".{$file}")) {
                return false;
            }
        }
        if (strpos(" {$page}", 'javascript:')) {
            return false;
        }
        return true;
    }

    public static function isSubdomain($domain, $baseDomain, $href)
    {
        $thisDomainOverBaseDomain = (sizeof($domain) > sizeof($baseDomain));
        $domainNotHaveWww = ($domain[0] !== 'www');
        $isNotBaseDomain = ($thisDomainOverBaseDomain AND $domainNotHaveWww);
        $newDomainString = ($baseDomain[0] == 'www') ? @"{$baseDomain[1]}.{$baseDomain[2]}" : @"{$baseDomain[0]}.{$baseDomain[1]}";

        $isNotOtherDomain = (strpos($href, "http://{$newDomainString}"));
        $isNotOtherDomainTwo = (strpos($href, "https://{$newDomainString}"));

        $pos = strpos($href, "{$newDomainString}");
        $isNotOtherDomainThree = ($pos !== false and $pos <= 20);
        $isNotOtherDomainCondition = ($isNotOtherDomain OR $isNotOtherDomainTwo OR $isNotOtherDomainThree);

        $serverUrl = ParserSettings::getCurrentDomain();
        $isNotScriptSubdomain = (!strpos($serverUrl, $newDomainString));

        return ($isNotBaseDomain AND $isNotOtherDomainCondition AND $isNotScriptSubdomain);
    }

    public static function isNotGoogleFiles($href)
    {
        return (strpos($href, 'google.com') === false AND strpos($href, 'googleapis.com') === false);
    }

    public function _thisPathIsSubdomain($page)
    {
        return $pos = strpos(" {$page}", 's__');
    }

    public function fileNotSaved($page)
    {
        $page = urldecode($page);
        return (CacheBackend::fileExists($page)) ? false : true;
    }

    public function isNotIgnoredCachePage($url)
    {
        // @me
        return true;
        $notCachePages = file(Constants::NOT_CACHED_FILE, FILE_IGNORE_NEW_LINES);
        if (!$notCachePages) {
            $notCachePages = array();
        }

        $urlIsIgnored = (in_array($url, $notCachePages));
        $urlWithoutEndSlash = substr($url, 0, strlen($url) - 1);
        $urlWithoutEndSlashIsIgnored = (in_array($urlWithoutEndSlash, $notCachePages));
        return ($urlIsIgnored OR $urlWithoutEndSlashIsIgnored) ? false : true;
    }
}
