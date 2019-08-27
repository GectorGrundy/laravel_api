<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;
use App\Models\Offer;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'with_already_added' => 'in:0,1',
            'only_my' => 'in:0,1',
            'per_page' => 'numeric|min:0|max:999',
            'page' => 'numeric|min:0',
            'search_type' => 'in:hash,title',
            'search' => 'string|min:1',
            'for' => 'in:offer_profile,offer_list',
            'country_ids' => 'sometimes|array',
            'country_ids.*' => 'numeric',
            'category_ids' => 'sometimes|array',
            'category_ids.*' => 'numeric',
            'source_ids' => 'sometimes|array',
            'currency_ids' => 'sometimes|array',
            'currency_ids.*' => 'numeric',
            'labels' => 'sometimes|array',
            'labels.*' => 'numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_get_list_error');
    }
}
