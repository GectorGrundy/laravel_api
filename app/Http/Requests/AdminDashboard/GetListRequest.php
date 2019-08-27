<?php
declare(strict_types=1);

namespace App\Http\Requests\AdminDashboard;

use App\Http\Requests\Request;
use App\Models\Currency;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'currency_id' => 'required|in:all,' . Currency::PAYOUT_CURRENCIES_STR,
            'period' => 'required|in:day,week,month'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('admin_statistics.on_get_list_error');
    }
}
