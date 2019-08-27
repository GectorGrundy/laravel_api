<?php
declare(strict_types=1);

namespace App\Http\Requests\CloakDomainPath;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:cloak_domain_paths,hash,deleted_at,NULL'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('cloak_domain_paths.on_delete_error');
    }
}
