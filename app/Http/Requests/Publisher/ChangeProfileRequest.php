<?php
declare(strict_types=1);

namespace App\Http\Requests\Publisher;

use App\Http\Requests\Request;
use App\Models\User;

class ChangeProfileRequest extends Request
{
    public function authorize()
    {
        $user = \Auth::user();
        if ($user->isSupport()) {
            $publisher_hash = $this->input('user_hash');

            if (empty($publisher_hash) || !$user->publisherBoundToSupport($publisher_hash)) {
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
        ];

        $user = \Auth::user();
        if ($user->isAdmin()) {
            $rules['user_hash'] = 'required|exists:users,hash,role,' . User::PUBLISHER;
            $rules['support_id'] = 'nullable|exists:support_profiles,user_id';
            $rules['comment'] = 'present|string|max:255';
            $rules['group_id'] = 'required|exists:user_groups,id,deleted_at,NULL';

        } elseif ($user->isPublisher()) {
            $rules['timezone'] = 'required|in:' . implode(',', config('app.timezones'));
            $rules['data_type'] = 'required|in:data,utm';
        }

        return $rules;
    }

    protected function getFailedValidationMessage()
    {
        return trans('users.on_change_profile_error');
    }
}
