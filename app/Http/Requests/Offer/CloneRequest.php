<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Http\Requests\Request;

class CloneRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:offers,id',
            'title' => 'required|max:255',
            'thumb_path' => 'required|max:255',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('offers.on_clone_error');
    }
}
