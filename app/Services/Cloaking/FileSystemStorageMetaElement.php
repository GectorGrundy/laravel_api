<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

use Exception;

class FileSystemStorageMetaElement extends OptionsGroup
{
    public function __construct($name, array $values = null)
    {
        $values['name'] = $name;
        parent::__construct($values, [
            'name' => ['type' => 'string,len_gt0'],
            'type' => ['type' => 'string,len_gt0'],
            'length' => ['type' => 'int,gt0,null'],
            'value' => [],
            'auto_increment' => ['type' => 'bool', 'value' => false],
            'key' => ['type' => 'string', 'value' => ''],
        ]);
        $t = $this->__get('type');
        $this->parsed_type = self::ParseType($t);
        if (self::TypeIsCompound($t)) throw new Exception("Invalid type '$t' for field '$name'");
        if ($this->__get('auto_increment') && !$this->IsInt()) throw new Exception("Incorrect column specifier 'auto_increment' for field '$name'");
        $this->parsed_type = $this->parsed_type->types;
        unset($this->parsed_type['null']);
        $this->parsed_type = key($this->parsed_type);
        if (('int' === $this->parsed_type /* || 'float' === $this->parsed_type */) && !$this->__get('length')) throw new Exception("Length is not specified for field '$name' (type '$this->parsed_type')");
    }

    final public function IsNullable()
    {
        return self::TypeIsNullable($this->__get('type'));
    }

    final public function IsUnsigned()
    {
        return self::TypeIsUnsigned($this->__get('type'));
    }

    final public function IsInt()
    {
        return self::TypeIsInt($this->__get('type'));
    }

    final public function IsFloat()
    {
        return self::TypeIsFloat($this->__get('type'));
    }

    final public function IsString()
    {
        return self::TypeIsString($this->__get('type'));
    }

    final public function IsBool()
    {
        return self::TypeIsBool($this->__get('type'));
    }

    final public function IsArray()
    {
        return self::TypeIsArray($this->__get('type'));
    }

    final public function GetSQLType()
    {
        $len = $this->__get('length');
        if ('string' === $this->parsed_type) return $len ? "varchar($len)" : 'text';
        if ('int' === $this->parsed_type) return "int($len)";
        return $this->parsed_type;
    }

    final public function CastValue($v)
    {
        if (null === $v && $this->IsNullable()) return $v;
        switch ($this->parsed_type) {
            case 'float':
                if (!is_float($v)) return (float)$v;
                break;
            case 'int':
                if (!is_int($v)) return (int)$v;
                break;
            case 'bool':
                if (!is_bool($v)) return (bool)$v;
                break;
            case 'string':
                if (!is_string($v)) return "$v";
                break;
            case 'array':
                if (!is_array($v)) throw new UnexpectedValueException('Value must be of the type array, ' . MSConfig::GetVarType($v) . ' given');
                break;
        }
        return $v;
    }

    final public function __debugInfo()
    {
        $r = [];
        foreach ($this as $k => $v) $r[$k] = $v;
        return $r;
    }

    private $parsed_type;
}
