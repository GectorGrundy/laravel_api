<?php
declare(strict_types=1);

namespace App\Services\Cloaking;


class OptionsGroup extends DataContainer
{
    public function __construct(array $values = null, array $meta)
    {
        if($values)
        {
            if(!$this->CheckArrayKeys($values, $meta, $diff)) throw new EDataContainerProperty($this->GetEUndefinedMsg(...$diff));
            foreach($values as $k => &$v) $meta[$k]['value'] = &$v;
        }
        parent::__construct($meta);
    }
}