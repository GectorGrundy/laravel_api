<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use stdClass;

class FileSystemStorage extends AbstractFileSystemStorage
{
    final public function __construct($file_name, array $options = null)
    {
        $this->AddOptionsMeta(['readonly' => ['type' => 'bool', 'value' => true]]);
        parent::__construct($file_name, $options);
    }

    final public function rewind()
    {
        reset($this->InitData()->data);
    }

    final public function key()
    {
        return key($this->InitData()->data);
    }

    final public function next()
    {
        next($this->InitData()->data);
    }

    final public function current()
    {
        $d = $this->InitData();
        return $this->GetRow($d, key($d->data));
    }

    final public function valid()
    {
        $d = $this->InitData();
        do {
            $k = key($d->data);
            if (null === $k) return false;
            if ($this->RowIsNotEmpty($d, $k)) return true;
            next($d->data);
        } while (1);
    }

    final public function __set($name, $value)
    {
        if (null === $value) $this->__unset($name);
        else {
            $this->CheckReadonly();
            if (is_array($value)) $type = 'array';
            elseif ($value instanceof stdClass) $type = 'object';
            else $type = false;
            if ($type) {
                $m = $this->GetMeta($d);
                $row = $this->GetRow($d, $name, $new)->SetDefaults();
                if ($pkey = $m->GetPrimaryKey()) {
                    if (is_array($pkey) > 1) throw new Exception('Can not use __set() with compound keys');
                    if ($this->HasKeyValue($pkey->name, $value, $type, $kval)) {
                        if (null === $kval) throw new Exception("Key '$pkey->name' cannot be null");
                        if ($m->{$pkey->name}->CastValue($name) !== $m->{$pkey->name}->CastValue($kval)) {
                            if ($new) throw new Exception("Key '$pkey->name' must be equal to the index");
                            $d->__data[$name] = null;
                            $d->changed = true;
                            $d->__data[$kval] = $row;
                            if (!array_key_exists($kval, $d->data)) $d->data[$kval] = null;
                        }
                    } else $row->{$pkey->name} = $name;
                }
                foreach ($value as $k => $v) $row->$k = $v;
            } else throw new Exception('Invalid type: ' . gettype($value) . '!');
        }
    }

    final public function &__get($name)
    {
        $d = $this->InitData();
        return $this->GetRow($d, $name);
    }

    final public function __isset($name)
    {
        $d = $this->InitData();
        return isset($d->data[$name]) || $d->changed;
    }

    final public function __unset($name)
    {
        $this->CheckReadonly();
        if (($pkey = $this->GetMeta($d)->GetPrimaryKey()) && is_array($pkey) > 1) throw new Exception('Can not use __unset() with compound keys');
        $d->__data[$name] = null;
        $d->changed = true;
    }

    final public function __invoke($value)
    {
        $this->CheckReadonly();
        if (is_array($value)) $type = 'array';
        elseif ($value instanceof stdClass) $type = 'object';
        else $type = false;
        if ($type) {
            $m = $this->GetMeta($d);
            if ($pkey = $m->GetPrimaryKey()) {
                if (is_array($pkey) > 1) throw new Exception('Can not use __set() with compound keys');
                if ($this->HasKeyValue($pkey->name, $value, $type, $kval) && null !== $kval) $k = $kval;
                elseif ($m->{$pkey->name}->IsInt() && $m->{$pkey->name}->auto_increment) {
                    $k = $this->GetAutoInc($d);
                    if ('array' === $type) $value[$pkey->name] = $m->{$pkey->name}->CastValue($k);
                    else $value->{$pkey->name} = $m->{$pkey->name}->CastValue($k);
                } else throw new Exception("Undefined key value '$pkey->name'");
            } else $k = $this->GetAutoInc($d);
            $d->changed = true;
            $d->__data[$k] = new FileSystemStorageRow($k, $this, $d);
            $d->data[$k] = null;
            foreach ($value as $f => $v) {
                if (null === $v && !$m->$f->IsNullable()) throw new Exception("Field '$f' cannot be null");
                $d->__data[$k]->$f = $m->$f->CastValue($v);
            }
            $this->Save($d);
            $d->changed = false;
            $this->Reload();
            return $k;
        } else throw new Exception('Invalid type: ' . gettype($value) . '!');
    }

    final public function Clear()
    {
        $this->CheckReadonly();
        $d = $this->InitData();
        foreach ($d->data as $k => $v) $d->__data[$k] = null;
        $d->changed = true;
    }

    final public function CheckReadonly()
    {
        if ($this->getOption('readonly')) throw new Exception('Object is readonly! Instance of ' . get_class($this) . ": '{$this->GetName()}'.");
    }

    final private function &GetRow(stdClass $d, $k, &$new = null)
    {
        $new = false;
        if (!array_key_exists($k, $d->__data)) {
            $d->__data[$k] = new FileSystemStorageRow($k, $this, $this->GetFiles());
            if ($new = !array_key_exists($k, $d->data)) $d->data[$k] = null;
        }
        return $d->__data[$k];
    }

    final private function HasKeyValue($name, $values, $type, &$kval)
    {
        if ('array' === $type) {
            if ($has_kval = array_key_exists($name, $values)) $kval = $values[$name];
        } elseif ($has_kval = (property_exists($values, $name) || isset($values->{$name}))) $kval = $values->{$name};
        return $has_kval;
    }

    final private function GetAutoInc(stdClass $d)
    {
        $d->data[] = true;
        end($d->data);
        return key($d->data);
    }
}