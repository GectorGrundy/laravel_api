<?php
declare(strict_types=1);

namespace App\Http\Requests\PublisherStatistic;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'currency_id'=> 'required|in:1,3,5',
            'period' => 'required|in:day,week,month'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('publisher_statistics.on_get_list_error');
    }
}
