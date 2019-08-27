<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Models\{
    Flow, FlowGroup, Landing, Scopes\HideDraftStatus, Transit
};
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Flow\Rules\PublisherCanManageByOfferPermissions;

class EditRequest extends Request
{
    public function rules(): array
    {
        return array_merge(Flow::$rules, [
            'hash' => 'required|exists:flows,hash,publisher_id,' . \Auth::id(),
            'target_hash' => 'required|exists:targets,hash',
            'offer_hash' => ['required', new PublisherCanManageByOfferPermissions()],
        ]);
    }

    public function messages(): array
    {
        return [
            'landings.required' => trans('flows.landings.required'),
            'tb_url.url' => trans('flows.tb_url.url'),
            'back_call_btn_sec.max' => trans('validation.max', [
                'attribute' => trans('messages.back_call')
            ]),
            'back_call_form_sec.max' => trans('validation.max', [
                'attribute' => trans('messages.back_action')
            ]),
            'vibrate_on_mobile_sec.max' => trans('validation.max', [
                'attribute' => trans('messages.vibrate_on_mobile')
            ]),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_edit_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {

            $group_id = 0;
            if ($this->filled('group_hash')) {
                $flow_group = FlowGroup::whereHash($this->input('group_hash'))->publisher(\Auth::user())->first();
                if ($flow_group === null) {
                    $validator->errors()->add('group_hash', trans('validation.exists', ['attribute' => 'group_hash']));
                    return;
                }
                $group_id = $flow_group['id'];
            }

            $target_id = \Hashids::decode($this->get('target_hash'))[0];
            $flow_id = \Hashids::decode($this->get('hash'))[0];

            $flow = Flow::withoutGlobalScope(HideDraftStatus::class)->find($flow_id);

            // Проверка, что выбранные лендинги принадлежат выбранному офферу/цели
            $landing_ids = (new Landing())->getIdsFromHashes($this->get('landings'));
            foreach ($landing_ids as $landing_id) {
                $exists = Landing::where('id', $landing_id)->where('offer_id', $flow->offer_id)
                    ->where('target_id', $target_id)
                    ->exists();

                if (!$exists) {
                    $validator->errors()->add('landing', trans('flows.landing.belongs_error'));
                }
            }
            // Проверка, что выбранные прелендинги принадлежат выбранному офферу/цели
            $transit_ids = (new Transit())->getIdsFromHashes($this->get('transits'));
            foreach ($transit_ids as $transit_id) {
                $exists = Transit::where('id', $transit_id)
                    ->where('offer_id', $flow->offer_id)
                    ->where('target_id', $target_id)
                    ->exists();

                if (!$exists) {
                    $validator->errors()->add('transit', trans('flows.transit.belongs_error'));
                }
            }

            $this->merge([
                'target_id' => $target_id,
                'extra_flow_id' => $this->getExtraFlowId(),
                'group_id' => $group_id,
            ]);
        });
    }

    private function getExtraFlowId(): int
    {
        $extra_flow_id = 0;
        if ($this->filled('extra_flow_hash')) {

            $decoded_data = \Hashids::decode($this->get('extra_flow_hash'));

            if (isset($decoded_data[0])) {
                try {
                    $extra_flow_info = Flow::findOrFail($decoded_data[0]);
                    $extra_flow_id = $extra_flow_info['id'];
                } catch (ModelNotFoundException $e) {

                }
            }
        }

        return $extra_flow_id;
    }
}
