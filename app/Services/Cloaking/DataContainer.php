<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use Exception;

class DataContainer extends AbstractDataContainer implements \Iterator, \JsonSerializable
{
    use TDataContainerTypes;

    public function __construct(array $meta)
    {
        foreach($meta as $k => $v)
        {
            $p = DataContainerElement::Create($k, $v);
            self::ParseAndTest($p->type, $p->value);
            $this->InitProperty($k, $p);
        }
        $this->meta = $meta;
    }

    public function __debugInfo()
    {
        $r = [];
        foreach($this->data as $k => $v) $r[$k] = ['value' => $v->value, 'type' => $v->type];
        return $r;
    }

    public function __clone()
    {
        throw new Exception('Can not clone instance of '.get_class($this));
    }

    final public function rewind() { reset($this->data); }
    final public function current() { return current($this->data)->value; }
    final public function key() { return key($this->data); }
    final public function next() { next($this->data); }
    final public function valid() { return null !== key($this->data); }

    final public function __set($name, $value)
    {
        if(isset($this->data[$name]))
        {
            if(false === $this->data[$name]->set) throw new EDataContainerProperty('Property '.get_class($this).'::$'.$name.' is read-only!');
            if(0 === $this->data[$name]->set) throw new EDataContainerProperty('Property '.get_class($this).'::$'.$name.' can be set only once!');
            if($this->data[$name]->type) self::TestValue($this->data[$name]->type, $value);
            if(1 === $this->data[$name]->set) --$this->data[$name]->set;
            if($this->data[$name]->proxy) $this->data[$name]->proxy->Set($value, $this->data[$name]);
            $this->data[$name]->value = $value;
            $this->data[$name]->has_value = true;
        }
        else throw new EDataContainerProperty($this->GetEUndefinedMsg($name));
    }

    final public function &__get($name)
    {
        $this->GetProperty($name);
        if($this->data[$name]->proxy)
        {
            $v = $this->data[$name]->value;
            $this->data[$name]->proxy->Get($v, $this->data[$name]);
            return $v;
        }
        if($this->data[$name]->set) return $this->data[$name]->value;
        else
        {
            $v = $this->data[$name]->value;
            return $v;
        }
    }

    final public function __isset($name)
    {
        return isset($this->data[$name]) && $this->data[$name]->has_value;
    }

    final public function __unset($name)
    {
        unset($this->data[$name]);
    }

    final public function PropertyIsDefault($name)
    {
        $d = $this->GetProperty($name);
        return array_key_exists('value', $this->meta[$name]) ? $d->value === $this->meta[$name]['value'] : false;
    }

    final public function PropertyIsNullable($name)
    {
        $d = $this->GetProperty($name);
        return $this->TypeIsNullable($d->type);
    }

    final public function PropertyIsUnsigned($name)
    {
        $d = $this->GetProperty($name);
        return $this->TypeIsUnsigned($d->type);
    }

    final public function jsonSerialize() { return $this->ToArray(); }

    final public function ToArray()
    {
        $r = [];
        foreach($this->data as $k => $v) $r[$k] = $v->value;
        return $r;
    }

    final private function InitProperty($name, DataContainerElement $data)
    {
        if(!is_string($name)) throw new EDataContainerInvalidMeta('Only string keys are allowed! '.gettype($name)."($name) given.");
        if(isset($this->data[$name])) throw new EDataContainerInvalidMeta('Duplicate property: '. __CLASS__ .'::$'.$name);
        $this->data[$name] = $data;
    }

    final private function GetProperty($name)
    {
        if(isset($this->data[$name]))
        {
            if(!$this->data[$name]->has_value && $this->data[$name]->init) throw new EDataContainerProperty('Uninitialized property: '.get_class($this).'::$'.$name);
            return $this->data[$name];
        }
        else throw new EDataContainerProperty($this->GetEUndefinedMsg($name));
    }

    private $data = [];
    private $meta;
}
