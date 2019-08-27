<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use stdClass;
use Exception;

class StdClassProxy extends stdClass implements \Iterator
{
    public function __construct(stdClass $data = null, array $options = null)
    {
        $this->options = new OptionsGroup($options, ['fields' => ['type' => 'array', 'value' => []], 'stdclass' => ['type' => 'string', 'value' => get_class($this)]]);
        $this->data = new stdClass;
        $this->data->{0} = $data ?: new stdClass;
        $this->data->{1} = new stdClass;
        if($this->options->fields) foreach($this->options->fields as $k) if(!isset($this->data->{0}->$k)) $this->data->{0}->$k = new stdClass;
        $this->data->fields = [];
        foreach($this->data->{0} as $k => $v)
        {
            $this->data->fields[$k] = new stdClass;
            $this->data->fields[$k]->i = 0;
            $this->data->fields[$k]->t = null;
        }
        $this->c = $this->options->stdclass;
    }

    final public function __get($name)
    {
        if(property_exists($this->data->{0}, $name))
        {
            if($this->data->fields[$name]->t) return $this->data->fields[$name]->t;
            if(false === $this->data->fields[$name]->t) return $this->data->{0}->$name;
            return ($this->data->fields[$name]->t = (is_object($this->data->{0}->$name) && 'stdClass' === get_class($this->data->{0}->$name))) ? ($this->data->fields[$name]->t = new $this->c($this->data->{0}->$name)) : $this->data->{0}->$name;
        }
        if(isset($this->data->{1}->$name)) return $this->data->{1}->$name;
    }

    final public function __set($name, $value)
    {
        if(property_exists($this->data->{0}, $name)) throw new Exception("Property '$name' is read only!");
        $this->data->{1}->$name = $value;
        if(!isset($this->data->fields[$name]))
        {
            $this->data->fields[$name] = new stdClass;
            $this->data->fields[$name]->i = 1;
            $this->data->fields[$name]->t = null;
        }
    }

    final public function Current()
    {
        $k = key($this->data->fields);
        if(null !== $k) return $this->data->{$this->data->fields[$k]->i}->$k;
    }

    final public function Key() { return key($this->data->fields); }
    final public function Next() { next($this->data->fields); }
    final public function Rewind() { reset($this->data->fields); }
    final public function Valid() { return null !== key($this->data->fields); }
    final public function __isset($name) { return property_exists($this->data->{0}, $name) || property_exists($this->data->{1}, $name); }

    final public function __unset($name)
    {
        unset($this->data->{0}->$name);
        unset($this->data->{1}->$name);
        unset($this->data->fields[$name]);
    }

    final public function __debugInfo()
    {
        $r = [];
        foreach([0, 1] as $i) foreach($this->data->$i as $k => $v) $r[$k] = $v;
        return $r;
    }

    private $data;
    private $options;
    private $c;
}
