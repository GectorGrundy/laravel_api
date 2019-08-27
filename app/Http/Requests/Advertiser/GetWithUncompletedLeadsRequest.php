<?php
declare(strict_types=1);

namespace App\Http\Requests\Advertiser;

use Auth;
use App\Http\Requests\Request;
use App\Models\User;

class GetWithUncompletedLeadsRequest extends Request
{
    public function rules()
    {
        return [
            'with' => 'array',
            'with.*' => 'in:profile',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_get_error');
    }
}
