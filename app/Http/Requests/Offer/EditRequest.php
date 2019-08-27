<?php
declare(strict_types=1);

namespace App\Http\Requests\Offer;

use App\Models\{
    Flow, Offer, Target
};
use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;

class EditRequest extends Request
{
    /**
     * @var Validator
     */
    private $validator;
    /**
     * @var Offer
     */
    private $offer;
    /**
     * @var ?string
     */
    private $error_message;

    public function rules()
    {
        return array_merge(Offer::$rules, [
            'id' => 'required|exists:offers,id,deleted_at,NULL',
            'status' => 'required|in:' . Offer::ACTIVE . ',' . Offer::INACTIVE . ',' . Offer::ARCHIVED,
            'thumb_path' => 'max:255',
        ]);
    }

    protected function getFailedValidationMessage()
    {
        return $this->error_message ?: trans('offers.on_edit_error');
    }

    public function messages(): array
    {
        return [
            'translations.*.title.required' => trans('offers.eng_title_required_error')

        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {

            // Не валидируем статусы, отличные от 'active'
            if ($this->input('status') !== Offer::ACTIVE) {
                return;
            }

            $this->validator = $validator;

            $this->offer = Offer::with([
                'targets.target_geo' => function ($q) {
                    $q->where('is_default', 1);
                },
                'targets.template',
                'targets.locale',
                'targets.target_geo.target_geo_rules' => function ($q) {
                    $q->where('is_fallback', 1);
                },
                'targets' => function ($q) {
                    $q->where('is_default', 1)->active();
                },
                'targets.landings' => function ($q) {
                    $q->where('is_private', 0);
                    $q->where('is_active', 1);
                }
            ])
                ->find($this->get('id'));

            if ($this->offer['status'] === Offer::INACTIVE) {
                $this->validateOfferActivation();
            }

            if ($this->input('is_private') != 0) {

                $flows = Flow::with('user')
                    ->where('status', 'active')
                    ->where('offer_id', $this->input('id'))->get();

                if ($flows->count()) {

                    $this->error_message = trans('offers.flow.exists');
                    addAccessErrorToValidator($validator, $flows);
                }
            }
        });
    }

    private function validateOfferActivation()
    {
        $this->offerHasTarget();

        foreach ($this->offer->targets as $target) {
            $this->targetHasActivePublicLanding($target);
            $this->targetHasTargetGeo($target);

            if ($target['landing_type'] === Target::INTERNAL_LANDING) {
                $this->targetHasRules($target);
            }
        }
    }

    private function offerHasTarget()
    {
        if (\count($this->offer->targets) < 1) {
            $this->validator->errors()->add('targets', trans('offers.targets.exists'));
        }
    }

    private function targetHasActivePublicLanding(Target $target)
    {
        if (\count($target['landings']) < 1) {
            $this->validator->errors()->add(
                'landings',
                $this->getTargetTitleFormatted($target) . ' - ' . trans('offers.target.landings.exists')
            );
        }
    }

    private function targetHasTargetGeo(Target $target)
    {
        if (\count($target['target_geo']) < 1) {
            $this->validator->errors()->add(
                'target_geo',
                $this->getTargetTitleFormatted($target) . ' - ' . trans('offers.target.target_geo.exists')
            );
        }
    }

    private function targetHasRules(Target $target)
    {
        foreach ($target['target_geo'] AS $target_geo) {
            if (\count($target_geo['target_geo_rules']) < 1) {
                $this->validator->errors()->add(
                    'target_geo_rules',
                    $target_geo['hash'] . ' - ' . trans('offers.target_geo_rules.exists')
                );
            }
        }
    }

    private function getTargetTitleFormatted(Target $target)
    {
        return "{$target['template']['title']} {$target['label']}";
    }
}
