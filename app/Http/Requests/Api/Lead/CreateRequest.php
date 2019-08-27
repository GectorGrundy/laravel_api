<?php
declare(strict_types=1);

namespace App\Http\Requests\Api\Lead;

use App\Classes\GeoInspector;
use App\Http\Requests\Request;
use App\Models\Country;
use App\Models\Flow;
use App\Models\TargetGeo;
use App\Services\LeadDoubleValidator;
use App\Services\PublisherApiDataContainer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreateRequest extends Request
{
    /**
     * @var string
     */
    private $ip;
    /**
     * @var array
     */
    private $geo;

    public function rules(): array
    {
        /**
         * @var PublisherApiDataContainer $data_container
         */
        $data_container = app(PublisherApiDataContainer::class);
        $publisher_id = $data_container->getPublisher()['id'];

        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'ip' => 'ip',
            'flow_hash' => 'required|exists:flows,hash,deleted_at,NULL,publisher_id,' . $publisher_id,
            'country_code' => 'string|size:2',
            'user_agent' => 'string',
            'referer' => 'string',
            'products' => 'json',
            'data1' => 'string|max:32',
            'data2' => 'string|max:32',
            'data3' => 'string|max:32',
            'data4' => 'string|max:32',
        ];
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function (Validator $validator) {

            $this->ip = $this->filled('ip') ? $this->input('ip') : $this->ip();
            $this->geo = (new GeoInspector())->getGeoIds($this->ip);
            $flow = (new Flow())->getByHash($this->input('flow_hash'));

            $target_geo = $this->getTargetGeo($flow);
            if (\is_null($target_geo)) {
                return $validator->errors()->add('target_geo', trans('api.target_geo.cannot_detect'));
            }

            $lead_hash = LeadDoubleValidator::validateApiLead($flow['id'], $target_geo['id'], $this->input('phone'));
            if (!\is_null($lead_hash)) {
                $validator->errors()->add('phone', trans('api.lead.is_double'));
                $validator->errors()->add('lead_hash', $lead_hash);
            }

            $this->merge([
                'target_geo' => $target_geo,
                'flow' => $flow,
                'ip' => $this->ip,
                'geo' => $this->geo,
            ]);
        });
    }

    protected function getFailedValidationMessage()
    {
        return trans('leads.on_create_error');
    }

    private function getTargetGeo(Flow $flow): ?TargetGeo
    {
        if (!$this->filled('country_code')) {
            return $this->getTargetGeoByIp($flow);
        }

        $country = (new Country())->getByCode(strtoupper($this->input('country_code')));

        $target_geo = $this->getTargetGeoByCountry($flow, $country['id']);
        if (\is_null($target_geo)) {
            $target_geo = $this->getTargetGeoByIp($flow);
        }

        return $target_geo;
    }

    private function getTargetGeoByIp(Flow $flow)
    {
        return $this->getTargetGeoByCountry($flow, $this->geo['country_id']);
    }

    private function getTargetGeoByCountry(Flow $flow, $country_id): ?TargetGeo
    {
        try {
            return (new TargetGeo())->getByTargetAndCountry($flow['target_id'], $country_id, $flow['publisher_id']);

        } catch (ModelNotFoundException $e) {
            return null;
        }
    }
}
