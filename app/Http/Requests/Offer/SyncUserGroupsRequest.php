<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Contracts\Validation\Validator;

class SyncUserGroupsRequest extends Request
{
    private $error_message;

    public function rules()
    {
        return [
            'offer_id' => 'required|exists:offers,id,deleted_at,NULL',
            'user_groups' => 'array',
            'user_groups.*' => 'size:1',
            'user_groups.*.user_group_id' => 'required|exists:user_groups,id',
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            $user_group_ids = collect($this->get('user_groups'))->pluck('user_group_id')->toArray();
            $offer_id = $this->get('offer_id');

            $selected_user_group_ids = \DB::table('offer_user_group')
                ->select('user_group_id')
                ->where('offer_id', $offer_id)
                ->get()
                ->pluck('user_group_id')
                ->toArray();

            $group_ids = array_diff($selected_user_group_ids, $user_group_ids);
            if (!\count($group_ids)) {
                return;
            }

            $user_ids = User::select('id')->whereIn('group_id', $group_ids)
                ->get()
                ->pluck('id')
                ->toArray();

            $flow = Flow::with(['user'])
                ->whereIn('publisher_id', $user_ids)
                ->where('status', 'active')
                ->where('offer_id', $offer_id)
                ->get();

            if ($flow->count()) {
                $this->error_message = trans('offers.user_group.flow');
                addAccessErrorToValidator($validator, $flow);
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_change_privacy_error');
    }
}