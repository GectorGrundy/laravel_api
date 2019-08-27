<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class GetByHashRequest extends Request
{
    public function rules()
    {
        return [
            'offer_hash' => 'required|exists:offers,hash,deleted_at,NULL',
            'with' => 'array',
            'with.*' => 'in:user_groups,publishers',
            'for' => 'in:offer_profile'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_get_error');
    }
}
