<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic\Reports;

use App\Http\Requests\Request;

class GetByOnlineOrderReportRequest extends Request
{
    public function rules()
    {
        return [
            'search_field' => 'in:external_key,order_id,phone,email,tracking_number,name',
            'search' => 'string',
            'currency_id' => 'required|exists:currencies,id',
        ];
    }

    public function messages()
    {
        return [
            'currency_id' => trans('statistics.currencies.incorrect'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('statistics.on_get_report_error');
    }
}