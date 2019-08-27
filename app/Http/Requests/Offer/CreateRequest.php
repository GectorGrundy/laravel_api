<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;
use App\Models\Offer;

class CreateRequest extends Request
{
    public function rules()
    {
        return array_merge(Offer::$rules, [
            'thumb_path' => 'required|max:255',
        ]);
    }

    public function messages(): array
    {
        return [
            'thumb_path.required' => trans('messages.thumb_path.required'),
            'translations.*.title.required' => trans('offers.eng_title_required_error')

        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_create_error');
    }
}
