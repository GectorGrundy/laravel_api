<?php
declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class BlockRequest extends Request
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    $query
                        ->where('status', User::ACTIVE)
                        ->whereIn('role', [User::PUBLISHER, User::ADVERTISER, User::SUPPORT, User::MANAGER]);
                }),
            ]
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_block_error');
    }
}
