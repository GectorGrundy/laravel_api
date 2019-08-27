<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferCategory;

use App\Http\Requests\Request;

class GetByIdRequest extends Request
{
    public function rules()
    {
        return [
            'offer_category_id' => 'required|numeric|exists:offer_categories,id',
            'with' => 'array',
            'with.*' => 'in:translations',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offer_categories.on_get_error');
    }
}
