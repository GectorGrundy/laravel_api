<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferCategory;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'offer_category_id' => 'required|numeric|exists:offer_categories,id',
            'title' => 'required|max:255',
            'title_en' => 'required|string',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offer_categories.on_edit_error');
    }
}
