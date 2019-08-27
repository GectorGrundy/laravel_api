<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

class NotTranslateAdapter implements TranslateAdapter
{
    public function setSource()
    {
    }

    public function setTarget()
    {
    }

    public function translate($string)
    {
        return $string;
    }
}
