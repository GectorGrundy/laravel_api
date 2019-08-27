<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class FileSystemStorageReadonly extends AbstractFileSystemStorage
{
    final public function rewind() { reset($this->InitData()->data); }
    final public function key() { return key($this->InitData()->data); }
    final public function next() { next($this->InitData()->data); }
    final public function valid() { return null !== key($this->InitData()->data); }
    final public function current() { if(null !== ($k = key($this->InitData()->data))) return $this->__get($k); }
    final public function __set($name, $value) { throw new Exception('Object is readonly! Instance of '.get_class($this).": '{$this->GetName()}'."); }
    final public function __get($name) { return (object)$this->InitData()->data[$name]; }
}