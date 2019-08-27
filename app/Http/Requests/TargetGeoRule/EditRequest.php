<?php
declare(strict_types=1);

namespace App\Http\Requests\TargetGeoRule;

use App\Http\Requests\Request;
use App\Models\TargetGeoRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class EditRequest extends Request
{
    public function rules()
    {
        $request = $this;

        return array_merge(TargetGeoRule::$rules, [
            'id' => 'required|exists:target_geo_rules,id,deleted_at,NULL',
            'advertiser_id' => [
                'required',
                'exists:users,id',
                Rule::unique('target_geo_rules', 'advertiser_id')
                    ->where(function (Builder $query) use ($request) {
                        return $query
                            ->where('id', '!=', $request->input('id'))
                            ->where('target_geo_id', $request->input('target_geo_id'))
                            ->whereNull('deleted_at');
                    })],
            'currency_id' => 'required|exists:accounts,currency_id,user_id,' . $this->input('advertiser_id'),
        ]);
    }

    public function messages(): array
    {
        return [
            'advertiser_id.unique' => trans('target_geo_rules.advertiser_id.unique'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('target_geo_rules.on_edit_error');
    }
}
