<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class FileSystemStorageRow extends \stdClass implements \Iterator, \JsonSerializable
{
    final public function __construct($id, FileSystemStorage $owner, \stdClass $row)
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->row = $row;
    }

    final public function rewind()
    {
        if(null === $this->meta) $this->meta = $this->owner->GetMeta();
        $this->meta->rewind();
    }

    final public function current() { return $this->GetValue($this->meta->key()); }
    final public function key() { return $this->meta->key(); }
    final public function next() { $this->meta->next(); }
    final public function valid() { return $this->meta->valid(); }
    final public function jsonSerialize() { return $this->__debugInfo(); }
    final public function Changed() { return $this->data; }

    final public function __set($name, $value)
    {
        if(!$this->owner->ColExists($name)) throw new Exception($this->GetEUndefinedMsg($name));
        $this->owner->CheckReadonly();
        if(null === $this->meta) $this->meta = $this->owner->GetMeta();
        if(null === $value && !$this->meta->$name->IsNullable()) throw new Exception("Field '$name' cannot be null");
        $this->data[$name] = $this->meta->$name->CastValue($value);
        $this->row->changed = true;
    }

    final public function __get($name)
    {
        if(!$this->owner->ColExists($name)) throw new Exception($this->GetEUndefinedMsg($name));
        return $this->GetValue($name);
    }

    final public function __isset($name)
    {
        return isset($this->data[$name]) || (isset($this->row->data[$this->id]) && isset($this->row->data[$this->id][$name]));
    }

    final public function __unset($name)
    {
        $this->owner->CheckReadonly();
        $this->data[$name] = null;
        $this->row->changed = true;
    }

    final public function __debugInfo()
    {
        if(null === $this->meta) $this->meta = $this->owner->GetMeta();
        $r = [];
        foreach($this->meta as $k => $v) $r[$k] = $this->GetValue($k);
        return $r;
    }

    final public function SetDefaults()
    {
        if(null === $this->meta) $this->meta = $this->owner->GetMeta();
        foreach($this->meta as $k => $v) $this->data[$k] = $this->meta->$k->value;
        return $this;
    }

    final protected function GetEUndefinedMsg($name) { return 'Undefined property: '.get_class($this).'::$'.$name; }

    final protected function GetValue($name)
    {
        if(array_key_exists($name, $this->data)) return $this->data[$name];
        if(isset($this->row->data[$this->id]) && isset($this->row->data[$this->id][$name])) return $this->row->data[$this->id][$name];
        if(null === $this->meta) $this->meta = $this->owner->GetMeta();
        return $this->meta->$name->value;
    }

    private $id;
    private $owner;
    private $data = [];
    private $meta = null;
    private $row;
}