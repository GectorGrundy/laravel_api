<?php
declare(strict_types=1);

namespace App\Http\Requests\DomainReplacement;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'domain_hash' => 'required|exists:domains,hash,user_id,' . \Auth::id(),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domain_replacements.on_get_list_error');
    }
}
