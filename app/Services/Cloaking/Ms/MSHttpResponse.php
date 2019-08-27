<?php
declare(strict_types=1);

namespace App\Services\Cloaking\Ms;

class MSHttpResponse
{
    final public function __construct($value, array $data, array $headers, array $cookie)
    {
        $this->value = $value;
        $this->data = $data;
        $this->headers = $headers;
        $this->cookie = $cookie;
    }

    final public function GetHeader($name)
    {
        $header = [];
        foreach($this->headers as $h)
        {
            $h = explode(':', $h, 2);
            if(2 === count($h) && 0 === strcasecmp($name, $h[0])) $header[] = ltrim($h[1]);
        }
        switch(count($header))
        {
            case 0: return;
            case 1: return $header[0];
            default: return $header;
        }
    }

    final public function __toString()
    {
        return "$this->value";
    }

    final public function __get($name)
    {
        if('url' === $name)
        {
            if(null === $this->url) $this->url = new MSURL($this->data['url']);
            return $this->url;
        }
        if('code' === $name) return $this->data['http_code'];
        if('mime' === $name || 'charset' === $name)
        {
            if(null === $this->content_type)
            {
                $this->content_type = new stdClass;
                $this->content_type->mime = $this->content_type->charset = null;
                if(empty($this->data['content_type'])) return;
                $a = explode(';', $this->data['content_type'], 2);
                $this->content_type->mime = strtolower($a[0]);
                if(!empty($a[1]))
                {
                    $s = 'charset=';
                    if(false !== ($pos = strpos($a[1], $s))) $this->content_type->charset = strtolower(trim(substr($a[1], $pos + strlen($s)), ' \'"'));
                }
            }
            return $this->content_type->$name;
        }
        if('content_type' === $name) return $this->data['content_type'];
        if('cookie' === $name) return $this->cookie;
        if('headers' === $name) return $this->headers;
        throw new Exception("Undefined property: $name");
    }

    final public function __debugInfo()
    {
        return ['url' => $this->__get('url'), 'code' => $this->__get('code'), 'content_type' => $this->__get('content_type')];
    }

    final public function __set($name, $value)
    {
        throw new Exception('Read only!');
    }

    final public function __unset($name)
    {
        throw new Exception('Read only!');
    }

    private $value;
    private $data;
    private $content_type = null;
    private $cookie;
    private $headers;
    private $url = null;
}
