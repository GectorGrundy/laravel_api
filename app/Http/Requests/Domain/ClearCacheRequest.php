<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use App\Http\Requests\Request;

class ClearCacheRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:domains,hash,deleted_at,NULL,user_id,' . \Auth::id(),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('domains.on_clear_cache_error');
    }
}
