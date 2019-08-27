<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;

abstract class Plugin
{
    protected $parser;
    protected $page_url;
    protected $page_name;
    protected $base_domain;
    protected $subdomain;
    protected $donor_url;
    protected $current_domain;

    public function setParams($page_url, $page_name, $subdomain)
    {
        $this->page_url = $page_url;
        $this->page_name = $page_name;
        $this->base_domain = Paths::getBaseDomainArray($page_url);
        $this->subdomain = $subdomain;
        $this->donor_url = ParserSettings::getDonorUrl();
        $this->current_domain = ParserSettings::getCurrentDomain();

        return $this;
    }

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    abstract public function run(simple_html_dom &$dom);
}
