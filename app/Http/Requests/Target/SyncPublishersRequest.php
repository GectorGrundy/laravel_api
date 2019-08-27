<?php
declare(strict_types=1);

namespace App\Http\Requests\Target;

use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;

class SyncPublishersRequest extends Request
{
    private $error_message;

    public function rules()
    {
        return [
            'target_id' => 'required|exists:targets,id,deleted_at,NULL',
            'publishers' => 'array',
            'publishers.*' => 'size:1',
            'publishers.*.publisher_id' => 'required|exists:users,id,role,' . User::PUBLISHER,
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            $publisher_ids = collect($this->get('publishers'))->pluck('publisher_id')->toArray();
            $target_id = $this->get('target_id');

            $selected_publisher_ids = \DB::table('target_publisher')
                ->select('publisher_id')
                ->where('target_id', $target_id)
                ->get()
                ->pluck('publisher_id')
                ->toArray();

            $removed_publisher_ids = array_diff($selected_publisher_ids, $publisher_ids);
            $flows = Flow::with(['user'])
                ->whereIn('publisher_id', $removed_publisher_ids)
                ->where('status', 'active')
                ->where('target_id', $target_id)
                ->get();

            if ($flows->count()) {
                $this->error_message = trans('targets.publisher.flow');
                addAccessErrorToValidator($validator, $flows);
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_edit_error');
    }
}