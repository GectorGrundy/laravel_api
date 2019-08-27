<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoRule;

use App\Http\Requests\Request;
use Illuminate\Http\Request AS R;
use App\Models\Country;

class GetListRequest extends Request
{
    // @todo remove it
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'target_geo_id' => 'sometimes|exists:target_geo,id',
            'with' => 'sometimes|array',
            'with.*' => 'in:integration,advertiser'
        ];
    }
}
