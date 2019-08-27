<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferCategory;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'is_adult' => 'in:0,1',
            'with' => 'array',
            'with.*' => 'in:translations',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offer_categories.on_get_list_error');
    }
}
