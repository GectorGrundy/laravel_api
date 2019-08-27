<?php
declare(strict_types=1);

namespace App\Http\Requests\Deposit;

use App\Http\Requests\Request;
use App\Models\Deposit;

class CreateRequest extends Request
{
	public function rules()
	{
		return array_merge(Deposit::$rules, [
            'with' => 'array',
            'with.*' => 'in:advertiser,advertiser.profile,admin',
            'created_at' => 'date_format:Y-m-d H:i:s'
        ]);
	}

	protected function getFailedValidationMessage()
	{
		return trans('deposits.on_create_error');
	}
}
