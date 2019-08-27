<?php
declare(strict_types=1);

namespace App\Providers;

use App\Mail\SendRegistrationEmail;
use App\Observers\Lead\DetectBrowser;
use App\Mail\SendRegeneratedUserPassword;
use App\Observers\Domain\SymlinkObserver;
use App\Observers\Domain\CreateNewServiceObserver;
use App\Observers\Target\DeleteTargetGeo;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\{
    CreateLeadStatusLog, CreateUserActivityLog, FlowRefreshShowedSitesCache, GenerateFlowGroupColor,
    InsertDataParameters, SetVisitorDataAfterSiteVisited, StatOnSiteVisited, LandingDomainsConfiguration,
    PaymentRequisiteLockAfterFirstPayment, PublisherStatisticUpdateHosts,
    SyncOfferAdvertisers, OnLeadCreated, OnLeadReverted,
    TransitDomainsConfiguration, OnLeadApproved, OnLeadCancelled, OnLeadTrashed,
    SendOrderTrackingNumberSms, SendPublisherPostback, OnLeadIntegrated, User\UserElasticIndex
};
use App\Observers\Order\FillFieldsFromInfoField;
use App\Models\{
    Domain, Lead, Order, Target
};
use App\Events\{
    Auth\Login, DomainEdited, Flow\FlowEdited, Go\SiteVisited, LandingCreated, LandingEdited,
    Offer\OfferCreated, Offer\OfferEdited, OrderTrackingNumberSet, PaymentPaid, TargetGeo\TargetGeoCreated,
    TargetGeo\TargetGeoDeleted, TargetGeo\TargetGeoEdited, TargetGeoRule\TargetGeoRuleCreated,
    TargetGeoRule\TargetGeoRuleDeleted, TargetGeoRule\TargetGeoRuleEdited, TransitCreated, TransitEdited,
    User\UserCreated, UserRegistered
};
use App\Events\Lead\{
    LeadChangedSubstatus, LeadCreated, LeadIntegrated, LeadApproved, LeadCancelled, LeadReverted, LeadTrashed
};
use App\Events\MyOfferCreated;
use App\Listeners\Auth\LogSuccessfulLogin;
use App\Listeners\OfferElasticIndex;
use App\Listeners\FlowElasticIndex;
use App\Events\DomainCreated;
use App\Listeners\DomainDetectCharset;
use App\Events\FlowGroupCreated;
use App\Events\Auth\UserPasswordRegenerated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        LandingCreated::class => [
            LandingDomainsConfiguration::class
        ],
        LandingEdited::class => [
            LandingDomainsConfiguration::class
        ],
        TransitCreated::class => [
            TransitDomainsConfiguration::class
        ],
        TransitEdited::class => [
            TransitDomainsConfiguration::class
        ],
        LeadCreated::class => [
            OnLeadCreated::class,
            SendPublisherPostback::class,
        ],
        LeadIntegrated::class => [
            OnLeadIntegrated::class
        ],
        LeadChangedSubstatus::class => [
            CreateLeadStatusLog::class,
        ],
        LeadApproved::class => [
            OnLeadApproved::class,
            SendPublisherPostback::class,
        ],
        LeadCancelled::class => [
            OnLeadCancelled::class,
            SendPublisherPostback::class,
        ],
        LeadTrashed::class => [
            OnLeadTrashed::class,
            SendPublisherPostback::class,
        ],
        LeadReverted::class => [
            OnLeadReverted::class,
        ],
        OrderTrackingNumberSet::class => [
            SendOrderTrackingNumberSms::class
        ],
        MyOfferCreated::class => [
            CreateUserActivityLog::class
        ],
        OfferCreated::class => [
            OfferElasticIndex::class
        ],
        OfferEdited::class => [
            OfferElasticIndex::class
        ],
        FlowEdited::class => [
            FlowElasticIndex::class,
            FlowRefreshShowedSitesCache::class,
        ],
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        UserRegistered::class => [
            SendRegistrationEmail::class,
            UserElasticIndex::class,
        ],
        UserCreated::class => [
            UserElasticIndex::class,
        ],
        PaymentPaid::class => [
            PaymentRequisiteLockAfterFirstPayment::class,
        ],
        SiteVisited::class => [
            SetVisitorDataAfterSiteVisited::class,
            PublisherStatisticUpdateHosts::class,
            InsertDataParameters::class,
            StatOnSiteVisited::class,
        ],
        DomainCreated::class => [
            DomainDetectCharset::class,
        ],
        FlowGroupCreated::class => [
            GenerateFlowGroupColor::class
        ],
        DomainEdited::class => [
            DomainDetectCharset::class,
        ],
        TargetGeoCreated::class => [
            SyncOfferAdvertisers::class
        ],
        TargetGeoEdited::class => [
            SyncOfferAdvertisers::class
        ],
        TargetGeoDeleted::class => [
            SyncOfferAdvertisers::class
        ],
        TargetGeoRuleCreated::class => [
            SyncOfferAdvertisers::class
        ],
        TargetGeoRuleEdited::class => [
            SyncOfferAdvertisers::class
        ],
        TargetGeoRuleDeleted::class => [
            SyncOfferAdvertisers::class
        ],
        UserPasswordRegenerated::class => [
            SendRegeneratedUserPassword::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     */
    protected $subscribe = [

    ];

    /**
     * Register any other events for your application.
     */
    public function boot()
    {
        Order::observe(FillFieldsFromInfoField::class);

        Lead::observe(DetectBrowser::class);
        Domain::observe(SymlinkObserver::class);
        Domain::observe(CreateNewServiceObserver::class);

        Target::observe(DeleteTargetGeo::class);

        parent::boot();
    }
}
