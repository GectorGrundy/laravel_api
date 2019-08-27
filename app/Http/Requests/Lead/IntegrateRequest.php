<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;
use App\Models\Lead;

class IntegrateRequest extends Request
{
    public function rules(): array
    {
        return [
            'lead_id' => 'required|numeric|exists:leads,id,type,' . Lead::COD_TYPE,
            'target_geo_rule_id' => 'required|numeric|exists:target_geo_rules,id',
        ];
    }

    protected function getFailedValidationMessage(): string
    {
        return trans('leads.on_integrate_error');
    }
}
