<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class GetCountByLabelsRequest extends Request
{
    public function rules()
    {
        return [
            'labels' => 'required|array',
            'labels.*' => 'numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_get_list_error');
    }
}
