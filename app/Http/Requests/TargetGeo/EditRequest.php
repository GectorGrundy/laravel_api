<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeo;

use App\Http\Requests\Request;
use App\Models\TargetGeo;

class EditRequest extends Request
{
	public function rules(): array
    {
		return array_merge(TargetGeo::$rules, [
			'id' => 'required|exists:target_geo,id,deleted_at,NULL',
            'target_geo_rule_sort_type' => 'required|in:' . TargetGeo::RULE_WEIGHT_SORT . ',' . TargetGeo::RULE_PRIOITY_SORT
        ]);
	}

    protected function getFailedValidationMessage()
    {
        return trans('target_geo.on_edit_error');
    }

}
