<?php
declare(strict_types=1);

namespace App\Contracts;

interface StatisticReportInterface
{
    public function generate();
}