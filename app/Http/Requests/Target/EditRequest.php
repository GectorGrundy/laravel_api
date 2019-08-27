<?php
declare(strict_types=1);

namespace App\Http\Requests\Target;

use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\Target;
use Illuminate\Contracts\Validation\Validator;

class EditRequest extends Request
{
    private $error_message;

    public function rules()
	{
		return array_merge(Target::$rules, [
			'id' => 'required|exists:targets,id,deleted_at,NULL',
        ]);
	}

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {

            if ($this->input('is_private') != 0) {

                $flows = Flow::where('status', 'active')
                    ->where('target_id', $this->input('id'))
                    ->get();

                if ($flows->count()) {
                    $this->error_message = trans('targets.flow.exists');
                    addAccessErrorToValidator($validator, $flows);
                }
            }
        });
    }

	protected function getFailedValidationMessage()
	{
        return $this->error_message ?: trans('offers.on_edit_error');
	}
}
