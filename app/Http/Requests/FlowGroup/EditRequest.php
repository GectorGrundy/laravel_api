<?php
declare(strict_types=1);

namespace App\Http\Requests\FlowGroup;

use App\Http\Requests\Request;

class EditRequest extends Request
{
    public function rules()
    {
        return [
            'title' => 'required|max:255',
        ];
    }
}
