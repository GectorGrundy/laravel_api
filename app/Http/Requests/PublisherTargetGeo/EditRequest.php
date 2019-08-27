<?php
declare(strict_types=1);

namespace App\Http\Requests\PublisherTargetGeo;

use App\Http\Requests\Request;
use App\Models\PublisherTargetGeo;

class EditRequest extends Request
{
    public function rules()
    {
        return array_merge(PublisherTargetGeo::$rules, [
            'id' => 'required|exists:publisher_target_geo,id',
        ]);
    }
}
