<?php
declare(strict_types=1);

namespace App\Http\Requests\Publisher;

use Auth;
use App\Http\Requests\Request;
use App\Models\UserPermission;


class GenerateApiKeyRequest extends Request
{
    public function authorize()
    {
        return UserPermission::userHasPermission(Auth::user()->id, UserPermission::API);
    }

    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('publishers.on_generate_api_key_error');
    }
}
