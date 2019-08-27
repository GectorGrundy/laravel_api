<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;

class CloneRequest extends Request
{
    public function rules()
    {
        return [
            'flow_hash' => 'required|exists:flows,hash,publisher_id,' . \Auth::id(),
            'title' => 'required|max:255',
            'clone_postbacks' => 'required|in:0,1',
        ];
    }


    public function messages()
    {
        return [
            'flow_title.required' => trans('flows.flow_title.required')
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_clone_error');
    }
}
