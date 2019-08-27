<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

trait TDataContainerTypes
{
    final protected static function ParseType($type) { return self::{self::$parse_type}($type); }

    final protected static function TestValue($type, $val)
    {
        $curr_type = false;
        if(self::$p_types_parsed[$type]->types)
        {
            $r = false;
            foreach(self::$p_types_parsed[$type]->types as $curr_type => $f) if($r = $f($val)) break;
            if(!$r) throw new EDataContainerInvalidValue(self::GetInvalidValueMsg($type, $val).' Must be '.(count(self::$p_types_parsed[$type]->types) > 1 ? 'one of the following types:' : 'of the type').' '.implode(', ', array_keys(self::$p_types_parsed[$type]->types)).'.');
        }
        foreach(self::$p_types_parsed[$type]->constr as $n => $f)
        {
            if(isset(self::$p_constraints[$n]->types[$curr_type]))
            {
                if(!$f($val)) throw new EDataContainerInvalidValue(self::GetInvalidValueMsg($type, $val)." Constraint `$n` violated.");
            }
            elseif('special' !== self::$p_types[$curr_type]->class && !array_intersect_key(self::$p_types_parsed[$type]->types, self::$p_constraints[$n]->types))
            {
                throw new EDataContainerInvalidMeta("Can not apply constraint `$n` for type `$curr_type`.");
            }
        }
    }

    final protected static function ParseAndTest($type, $val)
    {
        if($type)
        {
            self::ParseType($type);
            self::TestValue($type, $val);
        }
        // elseif();
    }

    final protected static function GetParsedType($type) { if($type) return self::$p_types_parsed[$type]; }

    final protected static function TypeIsNullable($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['null']);
    }

    final protected static function TypeIsUnsigned($type)
    {
        $t = self::GetParsedType($type);
        if($t && (isset($t->types['number']) || isset($t->types['float']) || isset($t->types['int']))) return isset($t->constr['gt0']) || isset($t->constr['gte0']);
    }

    final protected static function TypeIsCompound($type, $ignore_special_types = 'null')
    {
        $t = self::GetParsedType($type);
        if($ignore_special_types)
        {
            if(is_string($ignore_special_types))
            {
                $types = $t->types;
                if(self::TypeExists($ignore_special_types, 'special')) unset($types[$ignore_special_types]);
                else
                {
                    $t = explode(',', $ignore_special_types);
                    foreach($t as $v)
                        if(self::TypeExists($v, 'special')) unset($types[$v]);
                        else throw new Exception("Invalid type name '$v'; must be one of the following names: ".implode(', ', self::GetSpecialTypes()));
                }
            }
            elseif(true === $ignore_special_types) $types = array_diff_key($t->types, self::GetSpecialTypes());
            else throw new InvalidArgumentException('Argument 2 passed to '.__METHOD__.'() must be one of the following types: boolean or string; '.MSConfig::GetVarType($ignore_special_types).' given');
        }
        else $types = $t->types;
        return count($types) > 1;
    }

    final protected static function TypeIsInt($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['int']);
    }

    final protected static function TypeIsFloat($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['float']);
    }

    final protected static function TypeIsString($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['string']);
    }

    final protected static function TypeIsBool($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['bool']);
    }

    final protected static function TypeIsArray($type)
    {
        $t = self::GetParsedType($type);
        return !$t || isset($t->types['array']);
    }

    final protected static function TypeExists($name, $class = false) { return isset(self::$p_types[$name]) && (!$class || $class === self::$p_types[$name]->class); }

    final protected static function GetSpecialTypes()
    {
        $r = [];
        foreach(self::$p_types as $k => $t) if('special' === $t->class) $r[$k] = $k;
        return $r;
    }

    final private static function ParseDataElementType($type)
    {
        if(!isset(self::$p_types_parsed[$type]))
        {
            self::$p_types_parsed[$type] = $c = (object)['types' => [], 'constr' => []];
            $t = explode(',', $type);
            foreach($t as $i => $n)
            {
                if(isset(self::$p_types[$n]))
                {
                    if(isset($c->types[$n])) throw new EDataContainerInvalidMeta("Duplicate type `$n`");
                    $c->types[$n] = self::$p_types[$n]->test;
                    unset($t[$i]);
                }
                elseif(isset(self::$p_constraints[$n]))
                {
                    if(isset($c->constr[$n])) throw new EDataContainerInvalidMeta("Duplicate constraint `$n`");
                    $c->constr[$n] = self::$p_constraints[$n]->test;
                    unset($t[$i]);
                }
            }
            if($t) throw new EDataContainerInvalidMeta('Can not parse type info: '.implode(',', $t).'.');
            if($c->constr && !$c->types) foreach($c->constr as $n => $f) foreach(self::$p_constraints[$n]->types as $t) $c->types[$t] = self::$p_types[$t]->test;
        }
        return clone self::$p_types_parsed[$type];
    }

    final private static function InitAndParseDataElementType($p)
    {
        self::$p_types = [
            'callback' => (object)['test' => function($val){return is_callable($val);}, 'class' => 'pseudo'],
            'number' => (object)['test' => function($val){return is_int($val) || is_float($val);}, 'class' => 'pseudo'],
            'container' => (object)['test' => function($val){return ($val instanceof DataContainer);}, 'class' => 'simple'],
            'array' => (object)['test' => 'is_array', 'class' => 'simple'],
            'bool' => (object)['test' => 'is_bool', 'class' => 'simple'],
            'float' => (object)['test' => 'is_float', 'class' => 'simple'],
            'int' => (object)['test' => 'is_int', 'class' => 'simple'],
            'closure' => (object)['test' => function($val){return ($val instanceof Closure);}, 'class' => 'simple'],
            'stdclass' => (object)['test' => function($val){return ($val instanceof stdClass);}, 'class' => 'simple'],
            'iterator' => (object)['test' => function($val){return ($val instanceof Iterator);}, 'class' => 'simple'],
            'string' => (object)['test' => 'is_string', 'class' => 'simple'],
            'false' => (object)['test' => function($val){return false === $val;}, 'class' => 'special'],
            'true' => (object)['test' => function($val){return true === $val;}, 'class' => 'special'],
            'null' => (object)['test' => function($val){return null === $val;}, 'class' => 'special'],
        ];
        self::$p_constraints = [
            'gt0' => (object)['test' => function($val){return $val > 0;}, 'types' => ['float' => 'float', 'int' => 'int', 'number' => 'number', 'string' => 'string']],
            'gte0' => (object)['test' => function($val){return $val >= 0;}, 'types' => ['float' => 'float', 'int' => 'int', 'number' => 'number', 'string' => 'string']],
            'lt0' => (object)['test' => function($val){return $val < 0;}, 'types' => ['float' => 'float', 'int' => 'int', 'number' => 'number', 'string' => 'string']],
            'lte0' => (object)['test' => function($val){return $val <= 0;}, 'types' => ['float' => 'float', 'int' => 'int', 'number' => 'number', 'string' => 'string']],
            'cnt_gt0' => (object)['test' => function(array $val){return count($val) > 0;}, 'types' => ['array' => 'array']],
            'len_gt0' => (object)['test' => function($val){return '' !== "$val";}, 'types' => ['string' => 'string']],
        ];
        self::$parse_type = 'ParseDataElementType';
        return self::ParseDataElementType($p);
    }

    final private static function GetInvalidValueMsg($type, $val)
    {
        $t = gettype($val);
        return "Invalid value for type '$type': ".(is_scalar($val) ? var_export($val, true)." ($t)" : ('object' === $t ? 'instance of '.get_class($val) : $t)).'.';
    }

    private static $p_types = null;
    private static $p_constraints = null;
    private static $p_types_parsed = [];
    private static $parse_type = 'InitAndParseDataElementType';
}
