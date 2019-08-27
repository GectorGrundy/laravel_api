<?php
declare(strict_types=1);

namespace App\Http\Requests\Browser;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
	public function rules()
	{
        return [
            'search' => 'string',
            'ids' => 'array',
            'ids.*' => 'string',
        ];
    }

	protected function getFailedValidationMessage()
	{
		return trans('browser.on_get_list_error');
	}
}
