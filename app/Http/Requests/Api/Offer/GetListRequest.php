<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\Offer;

use App\Http\Requests\Request;
use App\Models\UserPermission;
use App\Models\PublisherProfile;

class GetListRequest extends Request
{
    protected function getFailedValidationMessage()
    {
        return trans('offers.on_get_list_error');
    }
}
