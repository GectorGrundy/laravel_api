<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;
use App\Models\Lead;

class BulkEditRequest extends Request
{
    public function rules()
    {
        return [
            'hashes' => 'required|array',
            'hashes.*' => 'string',
            'action' => 'required|in:approve,cancel,trash',
            'sub_status_id' => 'present|numeric',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_bulk_edit_error');
    }
}
