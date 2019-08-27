<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;
use App\Http\Requests\Flow\Rules\PublisherCanManageByOfferPermissions;

class CreateRequest extends Request
{
    public function rules(): array
    {
        return [
            'offer_hash' => [
                'required',
                'exists:offers,hash',
                new PublisherCanManageByOfferPermissions()
            ],
        ];
    }

    protected function getFailedValidationMessage(): string
    {
        return trans('flows.on_create_error');
    }
}
