<?php
declare(strict_types=1);

namespace App\Http\Requests\Go;

use App\Http\GoDataContainer;
use App\Services\GoUtmParameters;
use App\Services\CloakingService;
use Detection\MobileDetect;
use App\Classes\LandingHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request AS IlluminateRequest;
use App\Exceptions\Request\IncorrectParameterException;
use App\Classes\{
    PhoneInspector, GeoInspector, IpInspector
};
use App\Models\{
    Flow, Visitor, Landing, TargetGeo, Order, Lead, Locale, Country, Region, City
};

/**
 * Class GoRequest
 *
 * @todo Refactor this class
 */
class GoRequest
{
    protected $request;
    protected $locale;
    protected $lead;
    protected $target_geo;
    protected $order;
    protected $landing;
    protected $bot_inspector;
    protected $landing_handler;
    protected $visitor;
    protected $country;
    protected $region;
    protected $city;
    protected $mobile_detect;
    protected $phone_inspector;
    protected $geo_inspector;
    protected $ip_inspector;
    protected $flow;
    private $data_container;

    public function __construct(
        IlluminateRequest $request, Locale $locale_obj, Lead $lead, TargetGeo $target_geo, Order $order,
        Landing $landing, LandingHandler $landing_handler,
        Visitor $visitor, Country $country, Region $region, City $city, MobileDetect $mobile_detect,
        PhoneInspector $phone_inspector, GeoInspector $geo_inspector, IpInspector $ip_inspector,
        GoDataContainer $data_container
    )
    {
        $this->request = $request;
        $this->locale = $locale_obj;
        $this->lead = $lead;
        $this->target_geo = $target_geo;
        $this->order = $order;
        $this->landing = $landing;
        $this->landing_handler = $landing_handler;
        $this->visitor = $visitor;
        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        $this->mobile_detect = $mobile_detect;
        $this->phone_inspector = $phone_inspector;
        $this->ip_inspector = $ip_inspector;
        $this->flow = app()->make(Flow::class);
        $this->geo_inspector = $geo_inspector;
        $this->data_container = $data_container;
    }

    /**
     * Получение домена лендинга для установки на него куки
     *
     * @return string
     */
    public function getCookieDomain(): string
    {
        $domain = $this->data_container->getCurrentDomain();
        if ($domain->isParked()) {
            return $domain->domain;
        }

        if ($domain->is_custom && !$domain->entity->is_subdomain) {
            return $domain->domain;
        }

        return $this->removeSubdomainFromPathDomain($domain->domain);
    }

    private function removeSubdomainFromPathDomain($domain)
    {
        return substr($domain, strpos($domain, '.'));
    }

    /**
     * Получение списка ip адресов полетителя
     *
     * @return array
     */
    public function getIps(): array
    {
        return $this->ip_inspector->getIps();
    }

    /**
     * Получение target_geo_hash из запроса на оформления заказа
     *
     * @return string
     * @throws IncorrectParameterException
     */
    public function getTargetGeoHash(): ?string
    {
        return $this->request->get('target_geo_hash');
    }

    /**
     * Получение параметра name
     *
     * @return string
     */
    public function getClientParam(): string
    {
        $client = $this->request->input('client');
        if (empty($client)) {
            $client = trans('go.name_is_undefined');
        }
        return mb_substr($client, 0, 255);
    }

    /**
     * Получение параметра address
     *
     * @return string
     */
    public function getAddress(): string
    {
        return mb_substr($this->request->get('address', ''), 0, 255);
    }

    /**
     * Получение параметра email
     *
     * @return string
     */
    public function getEmailParam(): string
    {
        return mb_substr($this->request->get('email', ''), 0, 255);
    }

    /**
     * Получение параметра contact_type
     *
     * @return string
     */
    public function getContactType(): string
    {
        $contact_type = $this->request->input('contact_type');
        if (empty($contact_type) || !\in_array($contact_type, Order::CONTACT_TYPES)) {
            $contact_type = Order::CALL_CONTACT_TYPE;
        }
        return mb_substr($contact_type, 0, 255);
    }

    /**
     * Получение User Agent
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return mb_substr($this->request->header('User-Agent', ''), 0, 255);
    }

    /**
     * Получение параметра phone из запроса на оформления заказа
     *
     * @return string
     */
    public function getPhoneParam(): string
    {
        return $this->request->get('phone', '');
    }

    /**
     * Получение товаров из запроса на оформления заказа
     *
     * @return string
     */
    public function getProductsParam(): string
    {
        return $this->request->get('products', '{}');
    }

    /**
     * Получение товаров из запроса на оформления заказа
     *
     * @return string
     */
    public function getCustomParam(): string
    {
        if (!$this->request->filled('custom')) {
            return '{}';
        }

        $custom = json_encode($this->request->get('custom'));
        if (strlen($custom) > 65000) {
            return '{}';
        }

        return $custom;
    }

    public function getIsMobile(): int
    {
        return (int)$this->mobile_detect->isMobile();
    }

    public function getNameParam()
    {
        return $this->request->get('name', '');
    }

    public function getAgeParam()
    {
        return $this->request->get('age', '');
    }

    public function getLastNameParam()
    {
        return $this->request->get('last_name', '');
    }

    public function getStreetParam()
    {
        return $this->request->get('street', '');
    }

    public function getHouseParam()
    {
        return $this->request->get('house', '');
    }

    public function getApartment()
    {
        return $this->request->get('apartment', '');
    }

    public function getZipcodeParam()
    {
        return $this->request->get('zipcode', '');
    }

    public function getCityParam()
    {
        return $this->request->get('city', '');
    }

    public function getTargetGeoHashFromUrl()
    {
        return $this->request->get('target_geo_hash');
    }

    public function getRequiredUrlParams(array $with = []): array
    {
        $params = collect([
                's_id' => $this->data_container->getVisitor()['s_id'],
                GoUtmParameters::getParamName('data1') => $this->data_container->getData1(),
                GoUtmParameters::getParamName('data2') => $this->data_container->getData2(),
                GoUtmParameters::getParamName('data3') => $this->data_container->getData3(),
                GoUtmParameters::getParamName('data4') => $this->data_container->getData4(),
                'from' => $this->data_container->getFrom(),
                CloakingService::FOREIGN_PARAM => $this->data_container->getSite()['hash'],
                'flow_hash' => $this->data_container->getFlow()['hash'],
            ]
        )
            ->reject(function ($value, $key) {
                return empty($value);
            });

        $params = $params->merge($with);

        return $params->toArray();
    }

    public function getSuccessRedirect(string $lead_hash): RedirectResponse
    {
        return redirect()->route('cod_lead.success', array_merge([
            'lead_hash' => $lead_hash,
            'is_iframe' => $this->request->input('is_iframe', 0),
            'first' => 'true',
        ], $this->request->query()));
    }

    public function getCorrectRedirect(string $lead_hash): RedirectResponse
    {
        return redirect()->route('cod_lead.correct', array_merge([
            'lead_hash' => $lead_hash,
        ], $this->request->query()));
    }

    public function getIsBackaction()
    {
        if ($this->request->input('backaction', 0) == 1) {
            return 1;
        }
        return 0;
    }

    public function getIsBackcall()
    {
        if ($this->request->input('backcall', 0) == 1) {
            return 1;
        }
        return 0;
    }
}
