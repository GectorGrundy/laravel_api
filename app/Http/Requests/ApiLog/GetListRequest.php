<?php
declare(strict_types=1);

namespace App\Http\Requests\ApiLog;

use App\Http\Requests\Request;
use App\Models\BalanceTransaction;
use App\Models\Currency;

class GetListRequest extends Request
{
	public function rules()
	{
        return [
            'date_from' => 'required|string|date_format:Y-m-d',
            'date_to' => 'required|string|date_format:Y-m-d',
            'per_page' => 'numeric|min:0|max:200',
            'page' => 'numeric|min:0',
            'user_hashes' => 'array',
            'user_hashes.*' => 'string',
            'api_methods' => 'array',
            'api_methods.*' => 'string',
        ];
    }

	protected function getFailedValidationMessage()
	{
		return trans('api_logs.on_get_list_error');
	}
}
