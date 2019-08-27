<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

class ReplaceBaseLinks extends Plugin
{
    public function run(simple_html_dom &$dom)
    {
        $this->_addBaseHref($dom);
        $this->_addCanonical($dom);
    }

    private function _addBaseHref(&$dom)
    {
        $base = $dom->find('base', 0);
        $this->replaceLink($base);
    }

    private function replaceLink($link)
    {
        if ($link) {
            $url = ParserSettings::getDonorUrl();
            if ($url{strlen($url) - 1} == '/') {
                $url = substr($url, 0, -1);
            }
            $urlWithHttps = str_replace('http://', 'https://', $url);

            $link->href = str_replace(
                [
                    $url,
                    $urlWithHttps
                ],
                ParserSettings::getCurrentDomain(),
                $link->href
            );
        }
    }

    private function _addCanonical(&$dom)
    {
        $link = $dom->find("link[rel='canonical']", 0);
        $this->replaceLink($link);
    }
}
