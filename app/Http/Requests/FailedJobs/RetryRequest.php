<?php
declare(strict_types=1);

namespace App\Http\Requests\FailedJobs;

use App\Http\Requests\Request;
use App\Models\FailedJob;
use Illuminate\Contracts\Validation\Validator;

class RetryRequest extends Request
{

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $id = $this->input('id');

            if ($id !== 'all' && !FailedJob::find($id)) {
                $validator->errors()->add('id', trans('validation.in', [
                    'attribute' => 'id'
                ]));
            }
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('failed_jobs.on_retry_error');
    }
}
