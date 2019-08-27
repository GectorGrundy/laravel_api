<?php
declare(strict_types=1);

namespace App\Http\Requests\Transit;

use App\Http\Requests\RealpathRule;
use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\Transit;
use Illuminate\Contracts\Validation\Validator;

class EditRequest extends Request
{
    private $error_message;

    public function rules(): array
    {
		return array_merge(Transit::$rules, [
			'id' => 'required|exists:transits,id',
            'thumb_path' => 'string',
            'subdomain' => 'required|max:255|unique:transits,subdomain,' . $this->id . ',id'
                . '|unique:landings,subdomain',
            'realpath' => 'required|string',
        ]);
	}

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            if ($this->input('is_private') != 0) {

                $flows = Flow::with('user')->leftJoin('flow_transit', 'flows.id', '=', 'flow_transit.flow_id')
                    ->where('status', 'active')
                    ->where('transit_id', $this->input('id'))
                    ->get();

                if ($flows->count()) {

                    $this->error_message = trans('transit.flow.exists');
                    addAccessErrorToValidator($validator, $flows);
                }
            }

            (new RealpathRule())->validate($this, $validator);
        });
    }

	protected function getFailedValidationMessage()
	{
        return $this->error_message ?: trans('offers.on_edit_error');
	}
}
