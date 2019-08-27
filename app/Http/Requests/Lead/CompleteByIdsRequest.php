<?php
declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Request;
use App\Models\Advertiser;
use App\Models\AdvertiserProfile;
use App\Models\Lead;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;

class CompleteByIdsRequest extends Request
{
    public function rules()
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'numeric',
            'advertiser_id' => 'required|numeric',
            'rate' => 'required|numeric|min:0',
            'profit_at' => 'required|date_format:Y-m-d',
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_complete_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            $ids = $this->input('ids', []);

            /**
             * @var Collection $leads
             */
            $leads = Lead::with(['advertiser'])
                ->uncompleted()
                ->whereIn('id', $ids)
                ->where('advertiser_id', $this->input('advertiser_id'))
                ->get();

            // Не все выбранные лиды найдены
            if (\count($ids) !== $leads->count()) {
                return $validator->errors()->add('leads', trans('leads.incorrect_leads_count'));
            }

            // Если лиды в разных валютах выплаты паблишеру
            if ($leads->pluck('currency_id')->unique()->count() > 1) {
                return $validator->errors()->add('currency_id', trans('leads.have_different_currencies'));
            }

            // Если лиды в разных валютах стоимость лида реклу
            if ($leads->pluck('advertiser_currency_id')->unique()->count() > 1) {
                return $validator->errors()->add('advertiser_currency_id', trans('leads.have_different_advertiser_currencies'));
            }

            /**
             * @var Advertiser $advertiser
             */
            $advertiser = $leads[0]->advertiser;
            $account = $advertiser->getAccountByCurrencyId($leads[0]['advertiser_currency_id']);

            $advertiser_leads_payout = $leads->pluck('advertiser_payout')->sum();

            // Не достаточно системного баланса рекла для оплаты лидов
            if ($advertiser_leads_payout > $account['system_balance']) {
                return $validator->errors()->add('', trans('leads.not_enough_system_balance'));
            }

            $this->merge([
                'leads' => $leads,
            ]);
        });
    }
}
