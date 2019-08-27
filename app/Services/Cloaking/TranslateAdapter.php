<?php
declare(strict_types=1);

namespace App\Services\Cloaking;

interface TranslateAdapter
{
    public function setSource();

    public function setTarget();

    public function translate($string);
}