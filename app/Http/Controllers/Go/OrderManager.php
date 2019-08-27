<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Strategies\LeadCreation\CodLeadCreation;
use App\Http\Controllers\Controller;

/**
 * Прием запроса на генерацию лида на стороне нашего лендинга и передача соответствующему сервису
 */
class OrderManager extends Controller
{
    public function __invoke(CodLeadCreation $strategy)
    {
        return $strategy->handle();
    }
}