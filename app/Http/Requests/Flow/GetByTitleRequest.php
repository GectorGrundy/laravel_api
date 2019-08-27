<?php
declare(strict_types=1);

namespace App\Http\Requests\Flow;

use App\Http\Requests\Request;
use App\Models\Flow;
use App\Models\Landing;
use App\Models\Transit;
use Hashids;
use Auth;

class GetByTitleRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|string',
            'with.*' => 'in:user'
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('flows.on_get_error');
    }
}
