<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

abstract class AbstractDataContainer extends \stdClass
{
    public function __debugInfo()
    {
        $r = [];
        foreach($this->p as $k => $v) $r[$k] = $this->$k;
        return $r;
    }

    public function __get($name)
    {
        if(isset($this->p[$name])) return $this->$name;
        else throw new EDataContainerProperty($this->GetEUndefinedMsg($name));
    }

    final public static function CutOptions(array &$options = null, array $meta, $return_all = false)
    {
        $r = [];
        if($options)
            foreach($meta as $k => $m)
                if(array_key_exists($k, $options))
                {
                    $r[$k] = $options[$k];
                    unset($options[$k]);
                }
        return $return_all ? [$r, $meta] : $r;
    }

    final public static function SplitOptions(array $options = null, array ...$meta)
    {
        $r = [];
        foreach($meta as $i => $m)
        {
            $r[$i] = [];
            foreach($m as $k => $v) if(array_key_exists($k, $options)) $r[$i][$k] = $options[$k];
        }
        return $r;
    }

    final protected static function CheckArrayKeys(array $a, array $keys, array &$diff = null)
    {
        $diff = ($diff = array_diff_key($a, $keys)) ? array_keys($diff) : null;
        return !$diff;
    }

    final protected function GetEUndefinedMsg(...$names) { return 'Instance of '.get_class($this).' has undefined propert'.(count($names) > 1 ? 'ies' : 'y').': '.implode(', ', $names); }

    protected $name;
    protected $set;
    protected $has_value;
    protected $value;
    protected $type;
    protected $init;
    protected $proxy;

    private $p = ['name' => true, 'set' => true, 'has_value' => true, 'value' => true, 'type' => true, 'init' => true, 'proxy' => true];
}
