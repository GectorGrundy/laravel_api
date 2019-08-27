<?php
declare(strict_types=1);

namespace App\Services\Cloaking\Ms;

use Exception;

class MSURLTransform
{
    final public function __construct(array $data, $base_url = null)
    {
        foreach ($data as $host => $d) {
            if (!is_string($host) || '' === $host) throw new Exception('Host must be a non-empty string; ' . MSConfig::GetVarType($host) . ' given');
            if (is_string($d)) {
                $v = parse_url($d);
                if (false === $v || !array_filter($v)) throw new Exception('Invalid value: ' . MSConfig::GetVarType($d));
                $d = $v;
            }
            $d = array_merge(['scheme' => '', 'host' => '', 'path' => ''], $d);
            if ('' !== $d['path']) {
                $d['path'] = trim($d['path'], '/');
                if ('' !== $d['path']) $d['path'] = "/$d[path]";
            }
            $this->data[$host] = $d;
        }
        if ($base_url) {
            if (false === iconv_strpos($base_url, '//', 0, 'utf-8')) $base_url = MSConfig::GetProtocol() . $base_url;
            $this->base_url = new MSURL($base_url);
        }
    }

    final public function __invoke(MSURL &$url)
    {
        foreach ($this->data as $host => $d) {
            $u = $url->GetComponents();
            if ($host === $u->host || '*' === $host) {
                foreach ($d as $k => $v) if ('' !== $v) $u->$k = 'path' === $k ? $v . $u->$k : $v;
                if (null !== $this->base_url && '' !== $u->host && $this->base_url->host === $u->host && $this->base_url->scheme === $u->scheme) $u->scheme = $u->host = '';
                $url = new MSURL($u, $this->base_url);
                return true;
            }
        }
    }

    final public function __debugInfo()
    {
        return ['data' => $this->data, 'base_url' => null === $this->base_url ? null : "$this->base_url"];
    }

    private $data = [];
    private $base_url = null;
}
