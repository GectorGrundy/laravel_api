<?php
declare(strict_types=1);

namespace App\Exceptions\Cloaking;

use RuntimeException;

class DifferentHosts extends RuntimeException
{
    public function __construct()
    {
        $report_data = [
            'landing_hash' => request()->input('landing_info')['hash'],
            'flow_id' => request()->input('flow_info')['id'],
            'base_domain' => request()->input('base_domain'),
            'location_hostname' => request()->input('location_hostname'),
        ];

        parent::__construct(
            'При активном клоакинге потока обнаружен домен лендинга/прелендинга. Параметры: '
            . print_r($report_data, true)
        );
    }
}
