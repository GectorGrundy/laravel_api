<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\GoDataContainer;
use App\Classes\{
    GeoInspector, IpInspector
};
use App\Http\Requests\Go\GoRequest;
use App\Models\{
    Country, Flow, TargetGeo
};
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\TargetGeo\CannotDetectTargetGeo;

class VisitorTargetGeo
{
    /**
     * @var array
     */
    private $visitor;
    /**
     * @var GeoInspector
     */
    private $geo_inspector;
    /**
     * @var GoDataContainer
     */
    private $data_container;
    /**
     * @var IpInspector
     */
    private $ip_inspector;
    /**
     * @var Collection
     */
    private $target_geo_list;
    /**
     * @var Flow
     */
    private $flow;
    /**
     * @var string
     */
    private $x_forwarder_for_ip;
    /**
     * @var string
     */
    private $http_clientip;
    /**
     * @var string
     */
    private $remote_addr;
    /**
     * @var GoRequest
     */
    private $go_request;

    public function __construct(
        GoRequest $go_request, GeoInspector $geo_inspector, GoDataContainer $data_container,
        IpInspector $ip_inspector
    )
    {
        $this->geo_inspector = $geo_inspector;
        $this->data_container = $data_container;
        $this->ip_inspector = $ip_inspector;
        $this->go_request = $go_request;
    }

    public function handle(Request $request, \Closure $next)
    {
        $current_domain = $this->data_container->getCurrentDomain();
        $this->flow = $this->data_container->getFlow();

        // Its donor page of new cloaking
        if ($current_domain->isCloaked() && \is_null($this->flow)) {
            return $next($request);
        }

        $target_geo = $this->getTargetGeo();

        $this->data_container->setVisitorTargetGeo($target_geo);

        return $next($request);
    }

    private function getTargetGeo(): TargetGeo
    {
        $this->visitor = $this->data_container->getVisitor();

        $this->target_geo_list = (new TargetGeo())->getListByTargetId(
            (int)$this->flow['target_id'],
            [],
            (int)$this->flow['publisher_id']
        );

        if (!\is_null($target_geo = $this->getTargetGeoByUrlParameter())) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByCountryId((int)$this->visitor['geo_ids']['country_id']))) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByXForwarderFor())) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByHttpClient())) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByRemoteAddr())) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByBrowserLocale())) {
            return $target_geo;
        }

        if (!\is_null($target_geo = $this->getDefaultTargetGeo())) {
            $this->data_container->setIsFallbackTargetGeo(true);
            return $target_geo;
        }

        throw new CannotDetectTargetGeo($this->flow['target_id']);
    }

    private function getTargetGeoByUrlParameter(): ?TargetGeo
    {
        $target_geo_hash = $this->go_request->getTargetGeoHash();
        if (empty($target_geo_hash)) {
            return null;
        }

        if ($target_geo = $this->target_geo_list->where('hash', $target_geo_hash)->first()) {
            return $target_geo;
        }

        return null;
    }

    private function getTargetGeoByXForwarderFor(): ?TargetGeo
    {
        if (!$this->ip_inspector->issetXForwarderForIp() || !$this->ip_inspector->checkUserIpFromXForwarderFor()) {
            return null;
        }

        $this->x_forwarder_for_ip = $this->ip_inspector->getXForwarderForIp();

        // If already checked ip
        if ($this->x_forwarder_for_ip === $this->visitor['ip']) {
            return null;
        }

        $country_id = $this->geo_inspector->getGeoIds($this->x_forwarder_for_ip)['country_id'];
        if (empty($country_id)) {
            return null;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByCountryId($country_id))) {
            return $target_geo;
        }

        return null;
    }

    private function getTargetGeoByHttpClient(): ?TargetGeo
    {
        if (!$this->ip_inspector->issetClientIp()) {
            return null;
        }

        $this->http_clientip = $this->ip_inspector->getClientIp();

        // If already checked ip
        if (\in_array($this->http_clientip, [$this->visitor['ip'], $this->x_forwarder_for_ip])) {
            return null;
        }

        $country_id = $this->geo_inspector->getGeoIds($this->http_clientip)['country_id'];
        if (empty($country_id)) {
            return null;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByCountryId($country_id))) {
            return $target_geo;
        }

        return null;
    }

    private function getTargetGeoByRemoteAddr(): ?TargetGeo
    {
        $this->remote_addr = $this->ip_inspector->getRemoteAddrIp();

        // If already checked ip
        if (\in_array($this->remote_addr, [$this->visitor['ip'], $this->x_forwarder_for_ip, $this->http_clientip])) {
            return null;
        }

        $country_id = $this->geo_inspector->getGeoIds($this->remote_addr)['country_id'];
        if (empty($country_id)) {
            return null;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByCountryId($country_id))) {
            return $target_geo;
        }

        return null;
    }

    private function getTargetGeoByBrowserLocale(): ?TargetGeo
    {
        $browser_locale = $this->visitor['browser_locale'];
        try {
            $browser_locale_country = (new Country())->getByCode($browser_locale);
        } catch (ModelNotFoundException $e) {
            return null;
        }

        if ((int)$browser_locale_country['id'] === (int)$this->visitor['geo_ids']['country_id']) {
            return null;
        }

        if (!\is_null($target_geo = $this->getTargetGeoByCountryId($browser_locale_country['id']))) {
            return $target_geo;
        }

        return null;
    }


    private function getDefaultTargetGeo(): ?TargetGeo
    {
        return $this->target_geo_list->where('is_default', 1)->first();
    }

    private function getTargetGeoByCountryId(int $country_id): ?TargetGeo
    {
        if ($target_geo = $this->target_geo_list->where('country_id', $country_id)->first()) {
            return $target_geo;
        }
        return null;
    }
}
