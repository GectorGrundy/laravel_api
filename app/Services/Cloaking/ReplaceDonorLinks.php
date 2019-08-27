<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

class ReplaceDonorLinks extends Plugin
{
    public function run(simple_html_dom &$dom)
    {
        $str = (string)$dom;

        $donor_url = parse_url(ParserSettings::getDonorUrl(), PHP_URL_HOST);
        $script_domain = parse_url(ParserSettings::getCurrentDomain(), PHP_URL_HOST);


//        $re = '/(\/\/)' . $donor_url . '/iU';
//        $subst = "//{$script_domain}";
//        $str = preg_replace($re, $subst, $str);
//
//        $re = '/([^\/])' . $donor_url . '/iU';
//        $subst = "\${1}" . $script_domain;
//        $str = preg_replace($re, $subst, $str);

//        $str = str_replace("www.{$script_domain}", $script_domain, $str);

        $dom->load($str);
    }
}
