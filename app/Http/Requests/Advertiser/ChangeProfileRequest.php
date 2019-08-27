<?php
declare(strict_types=1);

namespace App\Http\Requests\Advertiser;

use App\Http\Requests\Request;
use App\Models\User;

class ChangeProfileRequest extends Request
{
    public function authorize()
    {
        $user = \Auth::user();
        if ($user->isManager()) {
            $advertiser_hash = $this->input('user_hash');

            if (empty($advertiser_hash) || !$user->advertiserBoundToManager($advertiser_hash)) {
                return false;
            }
        }

        return true;
    }

    public function rules()
    {
        $rules = [
            'full_name' => 'present|string|max:255',
            'skype' => 'present|string|max:255',
            'telegram' => 'present|string|max:255',
            'phone' => 'present|string|max:16',
            'whatsapp' => 'present|string|max:16',
            'interface_locale' => 'in:ru,en'
        ];

        $user = \Auth::user();
        if ($user->isAdmin()) {
            $rules['user_hash'] = 'required|exists:users,hash,role,' . User::ADVERTISER;
            $rules['manager_id'] = 'nullable|exists:users,id,role,' . User::MANAGER;
            $rules['info'] = 'present|string|max:255';

        } elseif ($user->isAdvertiser()) {
            $rules['timezone'] = 'required|in:' . implode(',', config('app.timezones'));
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_profile_error');
    }
}
