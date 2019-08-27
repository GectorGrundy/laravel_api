<?php
declare(strict_types=1);

namespace App\Http\Requests\PublisherTargetGeo;

use App\Http\Requests\Request;
use App\Models\PublisherTargetGeo;

class CreateRequest extends Request
{
    public function rules()
    {
        return array_merge(PublisherTargetGeo::$rules, [
            'offer_id' => 'required|exists:offers,id',
        ]);
    }
}
