<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Currency;
use Dingo\Api\Routing\Helpers;

class CurrencyController extends Controller
{
    use Helpers;

    public function getList()
    {
        $currencies = Currency::orderBy('id')->get();

        return ['response' => $currencies, 'status_code' => 200];
    }
}
