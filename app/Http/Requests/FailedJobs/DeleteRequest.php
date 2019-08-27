<?php
declare(strict_types=1);

namespace App\Http\Requests\FailedJobs;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:failed_jobs,id',
        ];
    }

    public function messages(): array
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('failed_jobs.on_delete_error');
    }
}
