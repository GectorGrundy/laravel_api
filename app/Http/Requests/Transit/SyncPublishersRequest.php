<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\Transit;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;

class SyncPublishersRequest extends Request
{
    private $error_message;

    public function rules()
    {
        return [
            'id' => 'required|exists:transits,id',
            'publishers' => 'array',
            'publishers.*' => 'size:1',
            'publishers.*.publisher_id' => 'required|exists:users,id,role,' . User::PUBLISHER,
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            $publisher_ids = collect($this->get('publishers'))->pluck('publisher_id')->toArray();

            $selected_publisher_ids = \DB::table('transit_user')
                ->select('publisher_id')
                ->where('transit_id', $this->get('id'))
                ->get()
                ->pluck('publisher_id')
                ->toArray();

            $removed_publisher_ids = array_diff($selected_publisher_ids, $publisher_ids) ? 1 : 0;

            $flows = Flow::with(['user'])
                ->leftJoin('flow_transit', 'flows.id', '=', 'flow_transit.flow_id')
                ->where('status', 'active')
                ->where('transit_id', $this->input('id'))
                ->where('publisher_id', $removed_publisher_ids ? $removed_publisher_ids : 0)
                ->get();

            if ($flows->count()) {

                $this->error_message = trans('transits.flow.exists');
                addAccessErrorToValidator($validator, $flows);
            }
        });
    }


    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_edit_error');
    }
}