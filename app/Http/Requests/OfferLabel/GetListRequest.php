<?php
declare(strict_types=1);

namespace App\Http\Requests\OfferLabel;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'with' => 'array',
            'with.*' => 'in:offers_count',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offer_labels.on_get_list_error');
    }
}
