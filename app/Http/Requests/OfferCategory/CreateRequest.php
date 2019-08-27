<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferCategory;

use App\Http\Requests\Request;

class CreateRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|unique:offer_categories,title|max:255',
            'title_en' => 'required|string',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offer_categories.on_create_error');
    }
}
