<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use simplehtmldom_1_5\simple_html_dom;
use Exception;

class PluginsContaier
{
    private $_pageUrl;
    private $_pageName;
    private $_subDomain;

    private $_plugins = array();

    public function setParams($pageUrl, $pageName, $subDomain = null)
    {
        $this->_pageUrl = $pageUrl;
        $this->_pageName = $pageName;
        $this->_subDomain = $subDomain;

        return $this;
    }

    public function addPlugin(Plugin $plugin)
    {
        $this->_plugins[] = $plugin;

        return $this;
    }

    public function run(simple_html_dom &$dom)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->setParams($this->_pageUrl, $this->_pageName, $this->_subDomain)
                    ->run($dom);
            } catch (Exception $e) {
            }
        }

        return $this;
    }
}
