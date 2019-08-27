<?php
declare(strict_types=1);

namespace App\Http\Requests\Landing;

use App\Http\Requests\Request;
use App\Models\Flow;
use Illuminate\Contracts\Validation\Validator;

class SyncPublishersRequest extends Request
{
    private $error_message;

    public function rules()
    {
        return [
            'id' => 'required|exists:landings,id',
            'publishers' => 'array',
            'publishers.*' => 'exists:users,id',
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            $publisher_ids = collect($this->get('publishers'))->pluck('publisher_id')->toArray();

            $selected_publisher_ids = \DB::table('landing_user')
                ->select('publisher_id')
                ->where('landing_id', $this->get('id'))
                ->get()
                ->pluck('publisher_id')
                ->toArray();

            $removed_publisher_ids = array_diff($selected_publisher_ids, $publisher_ids);

            $flows = Flow::with(['user'])
                ->leftJoin('flow_landing', 'flows.id', '=', 'flow_landing.flow_id')
                ->where('status', 'active')
                ->where('landing_id', $this->input('id'))
                ->where('publisher_id', $removed_publisher_ids ? $removed_publisher_ids : 0)
                ->get();

            if ($flows->count()) {

                $this->error_message = trans('landings.flow.exists');
                addAccessErrorToValidator($validator, $flows);
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_edit_error');

    }
}