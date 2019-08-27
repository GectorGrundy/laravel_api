<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowGroup;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'hash' => 'required|exists:flow_groups,hash'
        ];
    }
}
