<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;
use App\Models\Lead;

class GetUncompletedRequest extends Request
{
    public function rules()
    {
        return [
            'advertiser_id' => 'required|numeric|min:1',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_complete_error');
    }
}
