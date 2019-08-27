<?php
declare(strict_types=1);

namespace App\Http;

use App\Models\{
    CloakDomainPath, Domain, Flow, Landing, Locale, Offer, TargetGeo, Transit
};
use Carbon\Carbon;

class GoDataContainer
{
    /**
     * @var Domain
     */
    private $current_domain;
    /**
     * @var Landing
     */
    private $landing;
    /**
     * @var Transit
     */
    private $transit;
    /**
     * @var Flow
     */
    private $flow;
    /**
     * @var array
     */
    private $visitor;
    /**
     * @var CloakDomainPath
     */
    private $cloak_domain_path;
    /**
     * @var bool
     */
    private $is_bot;
    /**
     * @var Locale
     */
    private $locale;
    /**
     * @var Offer
     */
    private $offer;
    /**
     * @var bool
     */
    private $is_safepage;
    /**
     * @var string
     */
    private $data1;
    /**
     * @var string
     */
    private $data2;
    /**
     * @var string
     */
    private $data3;
    /**
     * @var string
     */
    private $data4;
    /**
     * @var string
     */
    private $clickid;
    /**
     * @var int
     */
    private $from_transit_id;
    /**
     * @var string
     */
    private $from_transit_traffic_type;
    /**
     * @var Carbon
     */
    private $flow_click_date;
    /**
     * @var bool
     */
    private $is_extra_flow;
    /**
     * @var bool
     */
    private $is_traffback;
    /**
     * @var TargetGeo
     */
    private $visitor_target_geo;
    /**
     * @var bool
     */
    private $is_fallback_target_geo = false;

    /**
     * @return bool
     */
    public function isFallbackTargetGeo(): bool
    {
        return $this->is_fallback_target_geo;
    }

    /**
     * @param bool $is_fallback_target_geo
     */
    public function setIsFallbackTargetGeo(bool $is_fallback_target_geo): void
    {
        $this->is_fallback_target_geo = $is_fallback_target_geo;
    }

    /**
     * @return TargetGeo
     */
    public function getVisitorTargetGeo(): TargetGeo
    {
        return $this->visitor_target_geo;
    }

    /**
     * @param TargetGeo $visitor_target_geo
     */
    public function setVisitorTargetGeo(TargetGeo $visitor_target_geo): void
    {
        $this->visitor_target_geo = $visitor_target_geo;
    }

    /**
     * @return bool
     */
    public function isTraffback(): bool
    {
        return $this->is_traffback ?? false;
    }

    /**
     * @param bool $is_traffback
     */
    public function setIsTraffback(bool $is_traffback): void
    {
        $this->is_traffback = $is_traffback;
    }

    /**
     * @return string
     */
    public function getData1(): string
    {
        return $this->data1;
    }

    /**
     * @param string $data1
     */
    public function setData1(string $data1)
    {
        $this->data1 = $data1;
    }

    /**
     * @return string
     */
    public function getData2(): string
    {
        return $this->data2;
    }

    /**
     * @param string $data2
     */
    public function setData2(string $data2)
    {
        $this->data2 = $data2;
    }

    /**
     * @return string
     */
    public function getData3(): string
    {
        return $this->data3;
    }

    /**
     * @param string $data3
     */
    public function setData3(string $data3)
    {
        $this->data3 = $data3;
    }

    /**
     * @return string
     */
    public function getData4(): string
    {
        return $this->data4;
    }

    /**
     * @param string $data4
     */
    public function setData4(string $data4)
    {
        $this->data4 = $data4;
    }

    /**
     * @return string
     */
    public function getClickid(): string
    {
        return $this->clickid;
    }

    /**
     * @param string $clickid
     */
    public function setClickid(string $clickid)
    {
        $this->clickid = $clickid;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * @var string
     */
    private $from;

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
    }

    /**
     * @return bool
     */
    public function isBot(): bool
    {
        return $this->is_bot;
    }

    /**
     * @param bool $is_bot
     */
    public function setIsBot(bool $is_bot): void
    {
        $this->is_bot = $is_bot;
    }

    /**
     * @return Domain
     */
    public function getCurrentDomain(): Domain
    {
        return $this->current_domain;
    }

    /**
     * @param Domain $current_domain
     */
    public function setCurrentDomain(Domain $current_domain): void
    {
        $this->current_domain = $current_domain;
    }

    /**
     * @return Landing
     */
    public function getLanding(): ?Landing
    {
        return $this->landing;
    }

    /**
     * @param Landing $landing
     */
    public function setLanding(Landing $landing): void
    {
        $this->landing = $landing;
    }

    /**
     * @return Transit
     */
    public function getTransit(): ?Transit
    {
        return $this->transit;
    }

    /**
     * @param Transit $transit
     */
    public function setTransit(Transit $transit): void
    {
        $this->transit = $transit;
    }

    /**
     * @return Flow
     */
    public function getFlow(): ?Flow
    {
        return $this->flow;
    }

    /**
     * @param Flow $flow
     */
    public function setFlow(?Flow $flow): void
    {
        $this->flow = $flow;
    }

    /**
     * @return array
     */
    public function getVisitor(): array
    {
        return $this->visitor;
    }

    /**
     * @param array $visitor
     */
    public function setVisitor(array $visitor): void
    {
        $this->visitor = $visitor;
    }

    /**
     * @return CloakDomainPath
     */
    public function getCloakDomainPath(): ?CloakDomainPath
    {
        return $this->cloak_domain_path;
    }

    /**
     * @param CloakDomainPath $cloak_domain_path
     */
    public function setCloakDomainPath(?CloakDomainPath $cloak_domain_path): void
    {
        $this->cloak_domain_path = $cloak_domain_path;
    }

    /**
     * @return Locale
     */
    public function getLocale(): Locale
    {
        return $this->locale;
    }

    /**
     * @param Locale $locale
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Landing|Transit
     */
    public function getSite()
    {
        if (!is_null($this->getTransit())) {
            return $this->getTransit();
        }

        if (!is_null($this->getLanding())) {
            return $this->getLanding();
        }
    }

    /**
     * @return bool
     */
    public function isSafepage(): ?bool
    {
        return $this->is_safepage ?? false;
    }

    /**
     * @param bool $is_safepage
     */
    public function setIsSafepage(bool $is_safepage)
    {
        $this->is_safepage = $is_safepage;
    }

    /**
     * @return int
     */
    public function getFromTransitId(): int
    {
        return $this->from_transit_id;
    }

    /**
     * @param int $from_transit_id
     */
    public function setFromTransitId(int $from_transit_id)
    {
        $this->from_transit_id = $from_transit_id;
    }

    /**
     * @return Carbon
     */
    public function getFlowClickDate(): Carbon
    {
        return $this->flow_click_date;
    }

    /**
     * @param Carbon $flow_click_datetime
     */
    public function setFlowClickDate(Carbon $flow_click_datetime)
    {
        $this->flow_click_date = $flow_click_datetime;
    }

    /**
     * @return string
     */
    public function getFromTransitTrafficType(): string
    {
        return $this->from_transit_traffic_type;
    }

    /**
     * @param string $from_transit_traffic_type
     */
    public function setFromTransitTrafficType(string $from_transit_traffic_type)
    {
        $this->from_transit_traffic_type = $from_transit_traffic_type;
    }

    /**
     * @return bool
     */
    public function isExtraFlow(): bool
    {
        return $this->is_extra_flow;
    }

    /**
     * @param bool $is_extra_flow
     */
    public function setIsExtraFlow(bool $is_extra_flow)
    {
        $this->is_extra_flow = $is_extra_flow;
    }
}