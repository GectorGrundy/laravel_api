<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

class ReplaceLinks extends Plugin
{
    public function run(simple_html_dom &$dom)
    {
        $clean_donor = str_replace(['https://', 'http://', 'www.'], '', $this->donor_url);
        $dom = str_replace([
            'https://www.' . $clean_donor,
            'http://www.' . $clean_donor,
            'https://' . $clean_donor,
            'http://' . $clean_donor,
            '//www.' . $clean_donor,
            '//' . $clean_donor
        ],
            $this->current_domain,
            $dom
        );

        $dom = Parser::_loadSimpleHtmlDom($dom);

//        $links = $dom->find('a');

//        foreach ($links as $link) {
//
//            if ($this->isNotHandledHref($link)) {
//                continue;
//            }
//
//            $domain = $this->getDomainsArray($link);
//            if (!is_null($domain)) {
//                $this->replaceLinksHref($this->base_domain, $this->subdomain, $domain, $link);
//            }
//        }
    }

    private function isNotHandledHref($link)
    {
        return
            !isset($link->href) ||
            empty($link->href) ||
            is_bool($link->href) ||
            (starts_with($link->href, '/') && substr($link->href, 0, 2) !== '//') ||
            starts_with($link->href, '#') ||
            strpos($link->href, 'mailto:') === 0 ||
            strpos($link->href, 'javascript:') === 0 ||
            strpos($link->href, 'javascript:') === 0 ||
            strpos($link->href, 'skype:') === 0;
    }

    public function replaceLinksHref($base_domain, $sub_domain, $domain_array, &$a)
    {
//        $url = $this->deleteProtocolAndFileNameFromUrl();
//        if ($url === '/') {
//            $url = '';
//        }
//        if($a->href === '//www.elconfidencial.com/espana/madrid/'){
//            dd($a->href);
//        }

//        if (!starts_with($a->href, '/')) {
            $this->replace($a);
//        }

//        if (ParserEquals::isSubdomain($domain_array, $base_domain, $a->href)) {
//            $a->href = Paths::subdomainPath($domain_array, $a->href);
//
//        } else if (ParserEquals::isRelativePath($a->href)) {
//            if ($a->href{0} !== '/' AND $a->href{0} !== '#') {
//                $a->href = "{$a->href}";
//            }
//            $a->href = $sub_domain . $a->href;
//        } else {
//
//            $linkHasNotStartSlash = ($a->href{0} !== '/');
//            if (strpos($a->href, 'independencia-155-puigdemont-rajoy-cataluna_1463326')) {
//                dd($a->href, $linkHasNotStartSlash, func_get_args(), 999);
//            }
//            if ($linkHasNotStartSlash) {
//                $this->deleteBaseUrlFromLink($a);
//                $a->href = "{$a->href}";
//            }
//        }
    }

    private function getDomainsArray($link)
    {
        $domain = parse_url($link->href, PHP_URL_HOST);
        if (is_null($domain)) {
            return null;
        }
        $domain = explode('.', $domain);
        return $domain;
    }

    private function deleteProtocolAndFileNameFromUrl()
    {
        $url = substr($this->page_url, strpos($this->page_url, '//') + 2);
        $url = explode('/', $url);
        unset($url[0]);
        unset($url[count($url)]);
        $url = implode('/', $url);

        return "/{$url}";
    }

    private function replace(&$a)
    {
//        echo $a->href . "<br>";
//        $host = '/';

        $clean_donor = str_replace(['https://', 'http://', 'www.'], '', $this->donor_url);

        $a->href = str_replace('https://www.' . $clean_donor, $this->current_domain, $a->href);
        $a->href = str_replace('http://www.' . $clean_donor, $this->current_domain, $a->href);
        $a->href = str_replace('https://' . $clean_donor, $this->current_domain, $a->href);
        $a->href = str_replace('http://' . $clean_donor, $this->current_domain, $a->href);
        $a->href = str_replace('//www.' . $clean_donor, $this->current_domain, $a->href);
        $a->href = str_replace('//' . $clean_donor, $this->current_domain, $a->href);

//        $url_without_www = str_replace('www.', '', $this->donor_url);
//        $url_without_https = str_replace(['https://www.', 'https://'], '', $this->donor_url);
//        $url_with_https = str_replace('http://', 'https://', $this->donor_url);
//        $url_with_http = str_replace('https://', 'http://', $this->donor_url);

//        $a->href = str_replace($url_with_https, $host, $a->href);
//        $a->href = str_replace($url_with_http, $host, $a->href);
//        $a->href = str_replace($url_without_https, $host, $a->href);
//        $a->href = str_replace($url_without_www, $host, $a->href);
//        $a->href = str_replace($this->donor_url, $host, $a->href);
//        dd($a->href, $clean_donor);
    }
}
