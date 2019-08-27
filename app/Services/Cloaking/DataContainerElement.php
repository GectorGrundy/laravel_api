<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class DataContainerElement extends AbstractDataContainer
{
    public static function Create($name, array $meta)
    {
        return new DataContainerElement($name, $meta);
    }

    protected function __construct($name, array $meta)
    {
        if(!self::CheckArrayKeys($meta, self::$p_defaults, $diff)) throw new EDataContainerInvalidMeta("Meta data '$name' has undefined option".(count($diff) > 1 ? 's' : '').': '.implode(', ', $diff));
        $this->name = $name;
        $this->set = isset($meta['set']) && (true === $meta['set'] || false === $meta['set'] || 1 === $meta['set']) ? $meta['set'] : self::$p_defaults['set'];
        if(isset($meta['type']))
        {
            $this->type = "$meta[type]";
            if('' === $this->type) throw new EDataContainerInvalidMeta("Empty type for property '$name'.");
        }
        else $this->type = self::$p_defaults['type'];
        if(isset($meta['proxy']))
        {
            if(!($meta['proxy'] instanceof IDataContainerElementProxy)) throw new EDataContainerInvalidMeta('Proxy must be of the type IDataContainerElementProxy, '.gettype($meta['proxy']).' given.');
            $this->proxy = $meta['proxy'];
        }
        $this->init = isset($meta['init']) && (true === $meta['init'] || false === $meta['init']) ? $meta['init'] : self::$p_defaults['init'];
        if($this->has_value = array_key_exists('value', $meta))
        {
            if($this->proxy) $this->proxy->Set($meta['value'], $this);
            $this->value = &$meta['value'];
        }
        else $this->value = self::$p_defaults['value'];
    }

    private static $p_defaults = ['init' => false, 'proxy' => false, 'set' => false, 'type' => null, 'value' => null];
}
