<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;
use App\Http\Requests\Flow\Rules\PublisherCanManageByOfferPermissions;

class EditVirtualRequest extends Request
{
    public function rules(): array
    {
        return [
            'flow_hash' => [
                'required',
                'exists:flows,hash,is_virtual,1',
            ],
            'landing_hash' => 'required|exists:landings,hash,deleted_at,NULL',
            'transit_hash' => 'sometimes|exists:transits,hash,deleted_at,NULL',
        ];
    }

    protected function getFailedValidationMessage(): string
    {
        return trans('flows.on_edit_error');
    }
}
