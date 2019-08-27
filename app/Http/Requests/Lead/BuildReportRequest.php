<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Models\{
    Currency, User
};
use App\Http\Requests\Request;
use App\Exceptions\User\UnknownUserRole;
use App\Strategies\Statistic\AdminReportStrategy;
use App\Strategies\Statistic\PublisherReportStrategy;
use Illuminate\Contracts\Validation\Validator;

class BuildReportRequest extends Request
{
    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var string
     */
    private $user_role;

    public function rules()
    {
        $this->user_role = \Auth::user()['role'];

        return $this->getRules();
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
        $user = \Auth::user();

        if ($user->isAdmin()) {
            $rules = $this->getAdminLevelFieldRules($level_field, $level_value);

        } else if ($user->isPublisher()) {
            $rules = $this->getPublisherLevelFieldRules($level_field, $level_value);

        } else {
            throw new UnknownUserRole($user['role'], ' for build report by leads.');
        }

        $validator = \Validator::make($this->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            foreach ($errors as $key => $error) {
                $this->validator->errors()->add($key, $error);
            }
        }
    }

    private function getAdminLevelFieldRules($level_field, $level_value)
    {
        switch ($level_field) {
            case AdminReportStrategy::CREATED_AT:
                return [$level_value => 'required|date'];

            case AdminReportStrategy::PROCESSED_AT:
            case AdminReportStrategy::PROCESSED_AT_HELD:
                return [$level_value => 'required'];

            case AdminReportStrategy::PUBLISHER_HASH:
            case AdminReportStrategy::ADVERTISER_HASH:
            case AdminReportStrategy::OFFER_HASH:
            case AdminReportStrategy::COUNTRY_ID:
            case AdminReportStrategy::TARGET_GEO:
                return [$level_value => 'required|string'];
        }
    }

    private function getPublisherLevelFieldRules($level_field, $level_value)
    {
        switch ($level_field) {
            case PublisherReportStrategy::DATETIME:
                return [$level_value => 'required|date'];

            case PublisherReportStrategy::OFFER_HASH:
            case PublisherReportStrategy::COUNTRY_ID:
            case PublisherReportStrategy::DATA1:
            case PublisherReportStrategy::DATA2:
            case PublisherReportStrategy::DATA3:
            case PublisherReportStrategy::DATA4:
                return [$level_value => 'required|string'];
        }
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_build_report_error');
    }

    private function getRules()
    {
        switch ($this->user_role) {
            case User::ADMINISTRATOR:
                return $this->getAdminRules();

            case User::PUBLISHER:
                return $this->getPublisherRules();

            default:
                throw new UnknownUserRole($this->user_role, ' for leads report.');
        }
    }

    private function getAdminRules()
    {
        $glued_group_fields = implode(',', [
            AdminReportStrategy::CREATED_AT,
            AdminReportStrategy::PROCESSED_AT,
            AdminReportStrategy::PROCESSED_AT_HELD,
            AdminReportStrategy::PUBLISHER_HASH,
            AdminReportStrategy::ADVERTISER_HASH,
            AdminReportStrategy::OFFER_HASH,
            AdminReportStrategy::COUNTRY_ID,
            AdminReportStrategy::TARGET_GEO,
        ]);

        return [
            'level' => 'required|in:1,2,3,4',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'advertiser_hashes' => 'array',
            'advertiser_hashes.*' => 'string',
            'target_geo_country_ids' => 'array',
            'target_geo_country_ids.*' => 'string',
            'publisher_hashes' => 'array',
            'publisher_hashes.*' => 'string',
            'offer_hashes' => 'array',
            'offer_hashes.*' => 'string',
            'group_field' => 'required|in:' . $glued_group_fields,
            'currency_ids' => 'array',
            'currency_ids.*' => 'in:' . implode(',', Currency::PAYOUT_CURRENCIES),

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
                    'title', 'real_approve', 'approve',
                    'total_count', 'approved_count', 'held_count', 'cancelled_count', 'trashed_count',
                    'rub_approved_payout', 'rub_held_payout', 'rub_profit',
                    'usd_approved_payout', 'usd_held_payout', 'usd_profit',
                    'eur_approved_payout', 'eur_held_payout', 'eur_profit',
                ])
        ];
    }

    private function getPublisherRules()
    {
        $glued_group_fields = implode(',', [
            PublisherReportStrategy::DATETIME,
            PublisherReportStrategy::OFFER_HASH,
            PublisherReportStrategy::COUNTRY_ID,
            PublisherReportStrategy::DATA1,
            PublisherReportStrategy::DATA2,
            PublisherReportStrategy::DATA3,
            PublisherReportStrategy::DATA4,
            PublisherReportStrategy::LANDING_HASH,
            PublisherReportStrategy::TRANSIT_HASH,
        ]);

        return [
            'level' => 'required|in:1,2,3,4',
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'country_ids' => 'array',
            'country_ids.*' => 'string',
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
                    'title', 'real_approve', 'approve',
                    'total_count', 'approved_count', 'held_count', 'cancelled_count', 'trashed_count',
                    'rub_approved_payout', 'rub_held_payout',
                    'usd_approved_payout', 'usd_held_payout',
                    'eur_approved_payout', 'eur_held_payout',
                ])
        ];
    }
}
