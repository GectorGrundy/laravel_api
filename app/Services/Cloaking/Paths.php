<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class Paths
{
    public static function getBaseDomainArray(string $page_url)
    {
        $base_domain = parse_url($page_url, PHP_URL_HOST);
        if (is_null($base_domain)) {
            return null;
        }
        $base_domain = explode('.', $base_domain);
        return $base_domain;
    }

    public static function getSitesFilesUrlForSubdomain(&$href)
    {

        #$href = urldecode($href);
        $href = self::str_replace_first($href, 'http://', '/o__');
        $href = self::str_replace_first($href, 'https://', '/o__');

        #$href = $self->rusToLat(str_replace(array('http://',
        #                                          'https://'), '/o__', urldecode($href), $count));
        return $href;
    }

    public static function str_replace_first($string, $search, $replace)
    {

        if ((($string_len = strlen($string)) == 0) || (($search_len = strlen($search)) == 0)) {
            return $string;
        }
        $pos = strpos($string, $search);

        if ($pos === 0) {
            return substr($string, 0, $pos) . $replace . substr($string, $pos + $search_len, max(0, $string_len - ($pos + $search_len)));
        }
        return $string;
    }

    public static function subdomainPath($domain, $href)
    {
        $domainString = $domain[sizeof($domain) - 2] . '.' . $domain[sizeof($domain) - 1];
        $array = explode($domainString, $href);
        $temp = array_pop($array);
        $temp = self::clearStartSlash($temp);
        $path = "/s__{$domain[0]}/" . $temp;

        return self::rusToLat(trim($path));
    }

    public static function clearStartSlash($path)
    {
        if ($path{0} == '/') {
            $path = substr($path, 1);
        }
        return $path;
    }

    public static function rusToLat($str)
    {
        $rus = array('Ð',
            'Ð‘',
            'Ð’',
            'Ð“',
            'Ð”',
            'Ð•',
            'Ð',
            'Ð–',
            'Ð—',
            'Ð˜',
            'Ð™',
            'Ðš',
            'Ð›',
            'Ðœ',
            'Ð',
            'Ðž',
            'ÐŸ',
            'Ð ',
            'Ð¡',
            'Ð¢',
            'Ð£',
            'Ð¤',
            'Ð¥',
            'Ð¦',
            'Ð§',
            'Ð¨',
            'Ð©',
            'Ðª',
            'Ð«',
            'Ð¬',
            'Ð­',
            'Ð®',
            'Ð¯',
            'Ð°',
            'Ð±',
            'Ð²',
            'Ð³',
            'Ð´',
            'Ðµ',
            'Ñ‘',
            'Ð¶',
            'Ð·',
            'Ð¸',
            'Ð¹',
            'Ðº',
            'Ð»',
            'Ð¼',
            'Ð½',
            'Ð¾',
            'Ð¿',
            'Ñ€',
            'Ñ',
            'Ñ‚',
            'Ñƒ',
            'Ñ„',
            'Ñ…',
            'Ñ†',
            'Ñ‡',
            'Ñˆ',
            'Ñ‰',
            'ÑŠ',
            'Ñ‹',
            'ÑŒ',
            'Ñ',
            'ÑŽ',
            'Ñ');
        $lat = array('A',
            'B',
            'V',
            'G',
            'D',
            'E',
            'E',
            'Gh',
            'Z',
            'I',
            'Y',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'F',
            'H',
            'C',
            'Ch',
            'Sh',
            'Sch',
            'Y',
            'Y',
            'Y',
            'E',
            'Yu',
            'Ya',
            'a',
            'b',
            'v',
            'g',
            'd',
            'e',
            'e',
            'gh',
            'z',
            'i',
            'y',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'f',
            'h',
            'c',
            'ch',
            'sh',
            'sch',
            'y',
            'y',
            'y',
            'e',
            'yu',
            'ya');

        return str_replace($rus, $lat, $str);
    }

    public static function getPageUrlForSubdomains($page, $baseDomain)
    {
        if (preg_match_all('/s__(.*)/', $page, $matches)) {
            $sub = explode('/', $matches[1][0]);
            $sub = $sub[0];
            $page = self::_getPage($page, $baseDomain, $sub);
        } else $sub = '';
        $subDomain = self::_returnSubDomainIfExists($sub);
        $page = str_replace('/index', '', $page);
        return array($page, $subDomain);
    }

    private static function _getPage($page, $baseDomain, $sub)
    {
        if ($baseDomain[0] == 'www') {
            $page = str_replace('www', $sub, $page);
            return $page;
        } else {
            $doubleSlashesCount = strpos($page, '//');
            if ($doubleSlashesCount === 0 OR $doubleSlashesCount > 0) {
                $tempUrl = substr($page, 0, $doubleSlashesCount + 2);
                $page = $tempUrl . str_replace(array($tempUrl,
                        "/s__{$sub}"), array("{$sub}.",
                        ''), $page);
                return $page;
            } else {
                $tempUrl = explode('//', ParserSettings::getDonorUrl());
                $tempUrl[1] = str_replace('/', '', $tempUrl[1]);
                $page = "http://{$sub}.{$tempUrl[1]}" . str_replace("s__{$sub}", '', $page);
                return $page;
            }
        }
    }

    private static function _returnSubDomainIfExists($sub)
    {
        if ($sub) {
            $subDomain = "/s__{$sub}";
            return $subDomain;
        }
    }

    public static function replaceSpecialChars($string, $decode = false)
    {
        $chars = Constants::$SPECIAL_CHARS;

        if (!$decode) {
            return urldecode(str_replace(array_keys($chars), array_values($chars), $string));
        }
        return str_replace(array_values($chars), array_keys($chars), $string);
    }

    public function _getUrlForOutDomain($page)
    {
        $page = str_replace('o__', 'http://', $page);
        return $page;
    }

    public function handleEndSlashInPath($path = null)
    {
        if (!$path || ends_with($path, '/')) {
            $path = $path . 'index';
        }
        return $path;
    }
}
