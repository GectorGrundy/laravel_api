<?php
declare(strict_types=1);

namespace App\Http\Requests\PublisherTargetGeo;

use App\Http\Requests\Request;

class GetListRequest extends Request
{
    public function rules()
    {
        return [
            'publisher_ids' => 'array',
            'offer_ids' => 'array',
        ];
    }
}
