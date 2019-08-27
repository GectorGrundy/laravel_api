<?php
declare(strict_types=1);

namespace App\Http\Requests\PublisherTargetGeo;

use App\Http\Requests\Request;

class DeleteRequest extends Request
{
    public function rules()
    {
        return [
            'id' => 'required|exists:publisher_target_geo,id'
        ];
    }
}
