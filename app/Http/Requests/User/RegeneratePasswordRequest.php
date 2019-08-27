<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class RegeneratePasswordRequest extends Request
{
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query->whereIn('role', [User::PUBLISHER, User::ADVERTISER, User::SUPPORT, User::MANAGER]);
                }),
            ],
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_passoword_error');
    }
}
