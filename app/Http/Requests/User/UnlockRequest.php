<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UnlockRequest extends Request
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query
                        ->where('status', User::LOCKED)
                        ->whereIn('role', [User::PUBLISHER, User::ADVERTISER, User::SUPPORT, User::MANAGER]);
                }),
            ]
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_unblock_error');
    }
}
