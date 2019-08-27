<?php
declare(strict_types=1);

namespace App\Http\Requests\Statistic;

use App\Models\Currency;
use App\Http\Requests\Request;
use App\Strategies\Statistic\PublisherDeviceReport;
use App\Strategies\Statistic\PublisherReportStrategy;
use Illuminate\Contracts\Validation\Validator;

class GetDeviceReportRequest extends Request
{
    /**
     * @var Validator
     */
    private $validator;

    public function rules()
    {
        $glued_group_fields = implode(',', [
            PublisherDeviceReport::DATETIME,
            PublisherDeviceReport::OFFER,
            PublisherDeviceReport::LANDING,
            PublisherDeviceReport::TRANSIT,
            PublisherDeviceReport::TARGET_GEO_COUNTRY,
            PublisherDeviceReport::DEVICE_TYPE,
            PublisherDeviceReport::OS_PLATFORM,
            PublisherDeviceReport::BROWSER,
            PublisherDeviceReport::DATA1,
            PublisherDeviceReport::DATA2,
            PublisherDeviceReport::DATA3,
            PublisherDeviceReport::DATA4,
        ]);


        return [
            'level' => 'required|in:1,2,3,4',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'country_ids' => 'array',
            'country_ids.*' => 'string',
            'target_geo_country_ids' => 'array',
            'target_geo_country_ids.*' => 'string',
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'string',
            'group_field' => 'required|in:' . $glued_group_fields,
            'currency_id' => 'required|in:' . implode(',', Currency::PAYOUT_CURRENCIES),
            'level_1_value' => 'required_if:level,2',
            'level_1_field' => 'required_if:level,2|in:' . $glued_group_fields . '|not_in:' . implode(',', [
                    $this->input('group_field', '')
                ]),
            'level_2_value' => 'required_if:level,3',
            'level_2_field' => 'required_if:level,3|in:' . $glued_group_fields . '|not_in:' . implode(',', [
                    $this->input('group_field', ''),
                    $this->input('level_1_field', ''),
                ]),
            'level_3_value' => 'required_if:level,4',
            'level_3_field' => 'required_if:level,4|in:' . $glued_group_fields . '|not_in:' . implode(',', [
                    $this->input('group_field', ''),
                    $this->input('level_1_field', ''),
                    $this->input('level_2_field', ''),
                ]),
            'sorting' => 'required|in:asc,desc',
            'sort_by' => 'required|in:' . implode(',', [
                    'title', 'total_count',
                    'real_approve', 'approve', 'approved_count', 'expected_approve',
                    'cancelled_count', 'trashed_count', 'safepage_count', 'bot_count',
                    'cr', 'cr_unique', 'epc', 'epc_unique',
                    'flow_hosts', 'hits', 'traffback_count',
                    'held_count', 'held_payout',
                ])
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $this->validator = $validator;

        $validator->after(function ($validation) {
            $level = (int)$this->input('level');

            if ($level >= 2) {
                $this->validateLevelValueByField($level - 1);
            }
            if ($level >= 3) {
                $this->validateLevelValueByField($level - 1);
            }
            if ($level >= 4) {
                $this->validateLevelValueByField($level - 1);
            }
        });
    }

    /**
     * Валидация родительских полей и значений в зависимости от уровня отчета
     *
     * @param int $level
     */
    private function validateLevelValueByField(int $level)
    {
        $level_field = $this->input('level_' . $level . '_field');
        $level_value = 'level_' . $level . '_value';

        $rules = $this->getPublisherLevelFieldRules($level_field, $level_value);


        $validator = \Validator::make($this->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            foreach ($errors as $key => $error) {
                $this->validator->errors()->add($key, $error);
            }
        }
    }

    private function getPublisherLevelFieldRules($level_field, $level_value)
    {
        switch ($level_field) {
            case PublisherReportStrategy::DATETIME:
                return [$level_value => 'required|date'];

            case PublisherDeviceReport::OFFER:
            case PublisherDeviceReport::TRANSIT:
            case PublisherDeviceReport::LANDING:
            case PublisherDeviceReport::TARGET_GEO_COUNTRY:
            case PublisherDeviceReport::OS_PLATFORM:
            case PublisherDeviceReport::BROWSER:
            case PublisherDeviceReport::DEVICE_TYPE:
                return [$level_value => 'required|string'];

            case PublisherDeviceReport::DATA1:
            case PublisherDeviceReport::DATA2:
            case PublisherDeviceReport::DATA3:
            case PublisherDeviceReport::DATA4:
                return [$level_value => 'present|string|max:32'];
        }
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_build_report_error');
    }
}
