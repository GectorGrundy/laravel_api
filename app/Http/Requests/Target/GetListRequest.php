<?php
declare(strict_types=1);

namespace App\Http\Requests\Target;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'exists:offers,hash',
            'with' => 'array',
            'with.*' => 'in:' . implode(',', [
                    'target_geo.country',
                    'target_geo.price_currency',
                    'landings.locale',
                    'landings.domain',
                    'transits.locale',
                    'transits.domain',
                    'template',
                    'locale',
                ])
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('targets.on_get_list_error');
    }
}
