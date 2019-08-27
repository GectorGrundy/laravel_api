<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class SyncCategoriesRequest extends Request
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'id' => 'required|exists:offers,id',
			'categories' => 'array',
			'categories.*' => 'exists:offer_categories,id',
		];
	}

	public function messages()
	{
		return [];
	}

	protected function getFailedValidationMessage()
	{
		return trans('offers.on_sync_categories_error');
	}
}
