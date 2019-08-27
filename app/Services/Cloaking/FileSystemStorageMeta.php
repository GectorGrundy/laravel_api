<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class FileSystemStorageMeta extends \stdClass implements \Iterator
{
    final public function __construct(AbstractFileSystemStorage $owner, array $data)
    {
        $this->owner = $owner;
        foreach ($data as $k => $v) {
            $this->data[$k] = new FileSystemStorageMetaElement($k, $v);
            if ($this->data[$k]->key) {
                if ('primary' === $this->data[$k]->key) {
                    if (!isset($this->keys[$this->data[$k]->key])) $this->keys[$this->data[$k]->key] = [];
                    $key = new stdClass;
                    $key->name = $k;
                    $this->keys[$this->data[$k]->key][] = $key;
                } else throw new Exception("Invalid type '{$this->data[$k]->key}' for key '$k'");
            }
        }
    }

    final public function rewind()
    {
        reset($this->data);
    }

    final public function current()
    {
        return current($this->data);
    }

    final public function key()
    {
        return key($this->data);
    }

    final public function next()
    {
        next($this->data);
    }

    final public function valid()
    {
        return null !== key($this->data);
    }

    final public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    final public function __set($name, $value)
    {
        throw new Exception('Read only!');
    }

    final public function __unset($name)
    {
        throw new Exception('Read only!');
    }

    final public function GetKeys()
    {
        return $this->keys;
    }

    final public function GetPrimaryKey()
    {
        if (null === $this->primary_key) {
            if (isset($this->keys['primary'])) {
                if (1 === count($this->keys['primary'])) $this->primary_key = $this->__get($this->keys['primary'][0]->name);
                else {
                    $this->primary_key = [];
                    foreach ($this->keys['primary'] as $k => $v) $this->primary_key[$v->name] = $this->__get($v->name);
                }
            } else $this->primary_key = false;
        }
        return $this->primary_key;
    }

    final public function __get($name)
    {
        if (array_key_exists($name, $this->data)) return $this->data[$name];
        throw new Exception('Undefined property: ' . get_class($this) . '::$' . $name);
    }

    public function __debugInfo()
    {
        $r = [];
        foreach ($this->data as $k => $v) $r[$k] = $v->type;
        return $r;
    }

    private $data = [];
    private $keys = [];
    private $primary_key = null;
    private $owner;
}