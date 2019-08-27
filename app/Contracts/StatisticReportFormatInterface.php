<?php
declare(strict_types=1);

namespace App\Contracts;

interface StatisticReportFormatInterface
{
    public function response($data);
}