<?php
declare(strict_types=1);

/**
 * @var Dingo\Api\Routing\Router $router
 */

$router->get('', ['uses' => 'HelloController@index']);
$router->get('trusted_proxies', ['middleware'=> ['trusted_proxies'], 'uses' => 'HelloController@trusted_proxies']);

Route::group(['middleware' => ['api.auth', 'cookie', 'session'], 'prefix' => 'translations'], function () {
    Vsch\TranslationManager\Translator::routes();
});
//Laravel\Horizon\Horizon::auth(function ($request) {
//    dd($request, \Auth::user());
//});
$router->group(['middleware' => ['publisher.api.auth']], function () use ($router) {
    $router->post('v1/lead.create', ['uses' => 'Api\LeadController@create']);
    $router->get('v1/lead.info', ['uses' => 'Api\LeadController@getInfo']);
    $router->get('v1/lead.getList', ['uses' => 'Api\LeadController@getList']);
});

// Postbacks
$router->group(['middleware' => ['postbackin.log']], function () use ($router) {
    $router->get('/rocketprofit', ['uses' => '\App\Integrations\RocketProfit\RocketProfitController@postback']);
    $router->get('/everad', ['uses' => '\App\Integrations\Everad\EveradController@postback']);
    $router->get('/terraleads', ['uses' => '\App\Integrations\Terraleads\TerraleadsController@postback']);
    $router->get('/leadbit', ['uses' => '\App\Integrations\Leadbit\LeadbitController@postback']);
    $router->get('/adcombo', ['uses' => '\App\Integrations\Adcombo\AdcomboController@postback']);
    $router->get('/kma', ['uses' => '\App\Integrations\Kma\KmaController@postback']);
    $router->get('/cpawebnet', ['uses' => '\App\Integrations\Cpawebnet\CpawebnetController@postback']);
    $router->get('/affbay', ['uses' => '\App\Integrations\Affbay\AffbayController@postback']);
    $router->get('/leadrock', ['uses' => '\App\Integrations\Leadrock\LeadrockController@postback']);
});

// Test
$router->get('device_tester', 'HelloController@deviceTester');

$router->group(['middleware' => ['cookie', 'session']], function () use ($router) {
    $router->post('login', 'Auth\LoginController@login');
    $router->post('registration', 'Auth\RegistrationController@register');
    $router->post('promoQuestion', 'Auth\RegistrationController@promoQuestion');
    $router->post('recoveryPasswordSend', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    $router->post('passwordReset', 'Auth\ResetPasswordController@passwordReset');
});

// Internal API
$router->group(
    ['middleware' => ['api.auth', 'api.log', 'auth_token.last_activity', 'api.locale']],
    function () use ($router) {

        $router->get('docs', function () {
            return \File::get(public_path('docs/index.html'));
        });
        $router->group(['middleware' => ['cookie', 'session']], function () use ($router) {
            $router->post('logout', 'Auth\LoginController@logout');
            $router->post('auth.loginAsUser', [
                'scopes' => 'auth.loginAsUser',
                'uses' => 'Auth\LoginController@loginAsUser'
            ]);
            $router->post('auth.logoutAsUser', [
                'scopes' => 'auth.logoutAsUser',
                'uses' => 'Auth\LoginController@logoutAsUser',
            ]);
        });

        $router->get('auth.getUser', ['scopes' => 'auth.getUser', 'uses' => 'Auth\LoginController@getUser']);

        //UserController
        $router->post('user.createPublisher', [
            'scopes' => 'user.createPublisher', 'uses' => 'UserController@createPublisher'
        ]);
        $router->post('user.createAdministrator', [
            'scopes' => 'user.createAdministrator', 'uses' => 'UserController@createAdministrator'
        ]);
        $router->post('user.createAdvertiser', [
            'scopes' => 'user.createAdvertiser', 'uses' => 'UserController@createAdvertiser'
        ]);
        $router->get('user.getList', ['scopes' => 'user.getList', 'uses' => 'UserController@getList']);
        $router->get('user.getByHash', ['scopes' => 'user.getByHash', 'uses' => 'UserController@getByHash']);
        $router->post('user.block', ['scopes' => 'user.block', 'uses' => 'UserController@block']);
        $router->post('user.unlock', ['scopes' => 'user.unlock', 'uses' => 'UserController@unlock']);
        $router->post('user.changePassword', [
            'scopes' => 'user.changePassword', 'uses' => 'UserController@changePassword'
        ]);

        $router->get('user.getBalance', ['scopes' => 'user.getBalance', 'uses' => 'UserController@getBalance']);
        $router->get('user.getStatisticSettings', [
            'scopes' => 'user.getStatisticSettings',
            'uses' => 'UserController@getStatisticSettings'
        ]);
        $router->post('user.updateStatisticSettings', [
            'scopes' => 'user.updateStatisticSettings',
            'uses' => 'UserController@updateStatisticSettings'
        ]);
        $router->post('user.regeneratePassword', [
            'scopes' => 'user.regeneratePassword',
            'uses' => 'UserController@regeneratePassword'
        ]);

        // PublisherController
        $router->get('user.getSourceList', [
            'scopes' => 'user.getSourceList', 'uses' => 'PublisherController@getSourceList'
        ]);
        $router->get('publisher.getList', [
            'scopes' => 'publisher.getList',
            'uses' => 'PublisherController@getList'
        ]);
        $router->get('publisher.getSummary', [
            'scopes' => 'publisher.getSummary',
            'uses' => 'PublisherController@getSummary'
        ]);
        $router->post('user.genApiKey', [
            'scopes' => 'user.genApiKey',
            'uses' => 'PublisherController@genApiKey'
        ]);
        $router->post('publisher.changeProfile', [
            'scopes' => 'publisher.changeProfile',
            'uses' => 'PublisherController@changeProfile'
        ]);

        // SupportController
        $router->get('support.getList', [
            'scopes' => 'support.getList',
            'uses' => 'SupportController@getList'
        ]);
        $router->post('support.changeProfile', [
            'scopes' => 'support.changeProfile',
            'uses' => 'SupportController@changeProfile'
        ]);
        $router->post('support.create', [
            'scopes' => 'support.create',
            'uses' => 'SupportController@create'
        ]);

        // ManagerController
        $router->get('manager.getList', [
            'scopes' => 'manager.getList',
            'uses' => 'ManagerController@getList'
        ]);
        $router->post('manager.changeProfile', [
            'scopes' => 'manager.changeProfile',
            'uses' => 'ManagerController@changeProfile'
        ]);
        $router->post('manager.create', [
            'scopes' => 'manager.create',
            'uses' => 'ManagerController@create'
        ]);

        // AdvertiserController
        $router->get('advertiser.getList', [
            'scopes' => 'advertiser.getList',
            'uses' => 'AdvertisersController@getList'
        ]);
        $router->get('advertiser.getSummary', [
            'scopes' => 'advertiser.getSummary',
            'uses' => 'AdvertisersController@getSummary'
        ]);
        $router->post('advertiser.changeProfile', [
            'scopes' => 'advertiser.changeProfile',
            'uses' => 'AdvertisersController@changeProfile'
        ]);
        $router->get('advertiser.getWithUncompletedLeads', [
            'scopes' => 'advertiser.getWithUncompletedLeads',
            'uses' => 'AdvertisersController@getWithUncompletedLeads'
        ]);
        $router->get('advertiser.getByHash', [
            'scopes' => 'advertiser.getByHash',
            'uses' => 'AdvertisersController@getByHash'
        ]);

        // AdministratorsController
        $router->get('administrators.getList', [
            'scopes' => 'administrators.getList',
            'uses' => 'AdministratorsController@getList'
        ]);
        $router->post('administrator.changeProfile', [
            'scopes' => 'administrator.changeProfile', 'uses' => 'AdministratorsController@changeProfile'
        ]);

        // FlowController
        $router->post('flow.create', ['scopes' => 'flow.create', 'uses' => 'FlowController@create']);
        $router->post('flow.createVirtual', [
            'scopes' => 'flow.createVirtual',
            'uses' => 'FlowController@createVirtual'
        ]);
        $router->post('flow.editVirtual', [
            'scopes' => 'flow.editVirtual',
            'uses' => 'FlowController@editVirtual'
        ]);
        $router->post('flow.edit', ['scopes' => 'flow.edit', 'uses' => 'FlowController@edit']);
        $router->post('flow.delete', ['scopes' => 'flow.delete', 'uses' => 'FlowController@delete']);
        $router->post('flow.clone', ['scopes' => 'flow.clone', 'uses' => 'FlowController@clone']);
        $router->get('flow.getByHash', ['scopes' => 'flow.getByHash', 'uses' => 'FlowController@getByHash']);
        $router->get('flow.getList', ['scopes' => 'flow.getList', 'uses' => 'FlowController@getList']);
        $router->get('flow.getTransitList', [
            'scopes' => 'flow.getTransitList', 'uses' => 'FlowController@getTransitList'
        ]);
        $router->get('flow.getLandingList', [
            'scopes' => 'flow.getLandingList', 'uses' => 'FlowController@getLandingList'
        ]);
        $router->get('flow.getByTitle', [
            'scopes' => 'flow.getByTitle', 'uses' => 'FlowController@getByTitle'
        ]);

        // PostbackController
        $router->post('postback.create', ['scopes' => 'postback.create', 'uses' => 'PostbackController@create']);
        $router->post('postback.edit', ['scopes' => 'postback.edit', 'uses' => 'PostbackController@edit']);
        $router->get('postback.getByHash', [
            'scopes' => 'postback.getByHash', 'uses' => 'PostbackController@getByHash'
        ]);
        $router->get('postback.getList', ['scopes' => 'postback.getList', 'uses' => 'PostbackController@getList']);
        $router->post('postback.delete', ['scopes' => 'postback.delete', 'uses' => 'PostbackController@delete']);

        // NewsController
        $router->post('news.create', ['scopes' => 'news.create', 'uses' => 'NewsController@create']);
        $router->post('news.edit', ['scopes' => 'news.edit', 'uses' => 'NewsController@edit']);
        $router->post('news.read', ['scopes' => 'news.read', 'uses' => 'NewsController@read']);
        $router->get('news.getByHash', ['scopes' => 'news.getByHash', 'uses' => 'NewsController@getByHash']);
        $router->get('news.getList', ['scopes' => 'news.getList', 'uses' => 'NewsController@getList']);
        $router->delete('news.delete', ['scopes' => 'news.delete', 'uses' => 'NewsController@delete']);

        // PaymentRequisitesController
        $router->post('payment_requisites.edit', [
            'scopes' => 'payment_requisites.edit', 'uses' => 'PaymentRequisitesController@edit'
        ]);
        $router->get('payment_requisites.getList', [
            'scopes' => 'payment_requisites.getList', 'uses' => 'PaymentRequisitesController@getList'
        ]);
        $router->get('payment_requisites.getListForPayment', [
            'scopes' => 'payment_requisites.getListForPayment', 'uses' => 'PaymentRequisitesController@getListForPayment'
        ]);

        //Payments
        $router->post('payment.create', [
            'scopes' => 'payment.create', 'uses' => 'PaymentsController@create'
        ]);
        $router->get('payment.getList', [
            'scopes' => 'payment.getList', 'uses' => 'PaymentsController@getList'
        ]);
        $router->post('payment.cancel', [
            'scopes' => 'payment.cancel', 'uses' => 'PaymentsController@cancel'
        ]);
        $router->post('payment.accept', [
            'scopes' => 'payment.accept', 'uses' => 'PaymentsController@accept'
        ]);
        $router->post('payment.pay', [
            'scopes' => 'payment.pay', 'uses' => 'PaymentsController@pay'
        ]);

        // PaymentSystem
        $router->post('payment_system.edit', [
            'scopes' => 'payment_system.edit', 'uses' => 'PaymentSystemController@edit'
        ]);
        $router->post('payment_system.syncPublishers', [
            'scopes' => 'payment_system.syncPublishers', 'uses' => 'PaymentSystemController@syncPublishers'
        ]);
        $router->get('payment_system.getList', [
            'scopes' => 'payment_system.getList', 'uses' => 'PaymentSystemController@getList'
        ]);

        // Countries
        $router->post('country.create', ['uses' => 'CountryController@create']);
        $router->post('country.edit', ['uses' => 'CountryController@edit']);
        $router->post('country.delete', ['uses' => 'CountryController@delete']);
        $router->get('country.getById', ['uses' => 'CountryController@getById']);
        $router->get('country.getList', ['uses' => 'CountryController@getList']);
        $router->get('country.getListForOfferFilter', ['uses' => 'CountryController@getListForOfferFilter']);

        // Offers
        $router->post('offer.create', ['scopes' => 'offer.create', 'uses' => 'OfferController@create']);
        $router->post('offer.edit', ['scopes' => 'offer.edit', 'uses' => 'OfferController@edit']);
        $router->post('offer.delete', ['scopes' => 'offer.delete', 'uses' => 'OfferController@delete']);
        $router->post('offer.clone', ['scopes' => 'offer.clone', 'uses' => 'OfferController@clone']);
        $router->get('offer.getByHash', ['scopes' => 'offer.getByHash', 'uses' => 'OfferController@getByHash']);
        $router->get('offer.getList', ['scopes' => 'offer.getList', 'uses' => 'OfferController@getList']);
        $router->get('offer.getCountByLabels', [
            'scopes' => 'offer.getCountByLabels',
            'uses' => 'OfferController@getCountByLabels'
        ]);
        $router->post('offer.syncCategories', [
            'scopes' => 'offer.syncCategories',
            'uses' => 'OfferController@syncCategories'
        ]);
        $router->post('offer.syncLabels', [
            'scopes' => 'offer.syncLabels',
            'uses' => 'OfferController@syncLabels'
        ]);
        $router->post('offer.syncPublishers', [
            'scopes' => 'offer.syncPublishers',
            'uses' => 'OfferController@syncPublishers'
        ]);
        $router->post('offer.syncUserGroups', [
            'scopes' => 'offer.syncUserGroups',
            'uses' => 'OfferController@syncUserGroups'
        ]);
        $router->post('offer.syncOfferSources', [
            'scopes' => 'offer.syncOfferSources',
            'uses' => 'OfferController@syncOfferSources'
        ]);
        $router->post('offer.addToMy', ['scopes' => 'offer.addToMy', 'uses' => 'OfferController@addToMy']);
        $router->post('offer.removeFromMy', [
            'scopes' => 'offer.removeFromMy', 'uses' => 'OfferController@removeFromMy'
        ]);

        // Offers Categories
        $router->post('offer_category.create', [
            'scopes' => 'offer_category.create',
            'uses' => 'OfferCategoryController@create'
        ]);
        $router->post('offer_category.edit', [
            'scopes' => 'offer_category.edit',
            'uses' => 'OfferCategoryController@edit'
        ]);
        $router->post('offer_category.delete', [
            'scopes' => 'offer_category.delete',
            'uses' => 'OfferCategoryController@delete'
        ]);
        $router->get('offer_category.getById', [
            'scopes' => 'offer_category.getById',
            'uses' => 'OfferCategoryController@getById'
        ]);
        $router->get('offer_category.getList', [
            'scopes' => 'offer_category.getList',
            'uses' => 'OfferCategoryController@getList'
        ]);
        $router->get('offer_category.getListForOfferFilter', [
            'scopes' => 'offer_category.getListForOfferFilter',
            'uses' => 'OfferCategoryController@getListForOfferFilter'
        ]);

        // Offers Sources
        $router->get('offer_source.getList', [
            'scopes' => 'offer.getList',
            'uses' => 'OfferSourceController@getList'
        ]);
        $router->get('offer_source.getListForOfferFilter', [
            'scopes' => 'offer_source.getListForOfferFilter',
            'uses' => 'OfferSourceController@getListForOfferFilter'
        ]);

        // Offers Requisite
        $router->post('offer_requisite.create', [
            'scopes' => 'offer_requisite.create',
            'uses' => 'OfferRequisiteController@create'
        ]);
        $router->post('offer_requisite.edit', [
            'scopes' => 'offer_requisite.edit',
            'uses' => 'OfferRequisiteController@edit'
        ]);
        $router->post('offer_requisite.delete', [
            'scopes' => 'offer_requisite.delete',
            'uses' => 'OfferRequisiteController@delete'
        ]);

        // TicketController
        $router->post('ticket.create', ['scopes' => 'ticket.create', 'uses' => 'TicketController@create']);
        $router->post('ticket.close', ['scopes' => 'ticket.close', 'uses' => 'TicketController@close']);
        $router->post('ticket.open', ['scopes' => 'ticket.open', 'uses' => 'TicketController@open']);
        $router->get('ticket.getList', ['scopes' => 'ticket.getList', 'uses' => 'TicketController@getList']);
        $router->get('ticket.getByHash', ['scopes' => 'ticket.getByHash', 'uses' => 'TicketController@getByHash']);
        $router->post('ticket.defer', ['scopes' => 'ticket.defer', 'uses' => 'TicketController@defer']);
        $router->post('ticket.markAsRead', [
            'scopes' => 'ticket.markAsRead',
            'uses' => 'TicketController@markAsRead'
        ]);

        // TicketMessageController
        $router->post('ticket_messages.create', [
            'scopes' => 'ticket_messages.create',
            'uses' => 'TicketMessageController@create'
        ]);

        // Landing
        $router->post('landing.create', ['scopes' => 'landing.create', 'uses' => 'LandingController@create']);
        $router->post('landing.edit', ['scopes' => 'landing.edit', 'uses' => 'LandingController@edit']);
        $router->post('landing.delete', ['scopes' => 'landing.delete', 'uses' => 'LandingController@delete']);
        $router->get('landing.getList', [
            'scopes' => 'landing.getList',
            'uses' => 'LandingController@getList'
        ]);
        $router->get('landing.getByHash', [
            'scopes' => 'landing.getByHash',
            'uses' => 'LandingController@getByHash'
        ]);
        $router->post('landing.syncPublishers', [
            'scopes' => 'landing.syncPublishers',
            'uses' => 'LandingController@syncPublishers'
        ]);

        // Transit
        $router->post('transit.create', ['scopes' => 'transit.create', 'uses' => 'TransitController@create']);
        $router->post('transit.edit', ['scopes' => 'transit.edit', 'uses' => 'TransitController@edit']);
        $router->post('transit.delete', ['scopes' => 'transit.delete', 'uses' => 'TransitController@delete']);
        $router->get('transit.getList', ['scopes' => 'transit.getList', 'uses' => 'TransitController@getList']);
        $router->get('transit.getByHash', ['scopes' => 'transit.getByHash', 'uses' => 'TransitController@getByHash']);
        $router->post('transit.syncPublishers', [
            'scopes' => 'transit.syncPublishers',
            'uses' => 'TransitController@syncPublishers'
        ]);

        // Target
        $router->post('target.create', ['scopes' => 'target.create', 'uses' => 'TargetController@create']);
        $router->post('target.edit', ['scopes' => 'target.edit', 'uses' => 'TargetController@edit']);
        $router->post('target.delete', ['scopes' => 'target.delete', 'uses' => 'TargetController@delete']);
        $router->get('target.getList', ['scopes' => 'target.getList', 'uses' => 'TargetController@getList']);
        $router->post('target.syncPublishers', [
            'scopes' => 'target.syncPublishers',
            'uses' => 'TargetController@syncPublishers'
        ]);
        $router->post('target.syncUserGroups', [
            'scopes' => 'target.syncUserGroups',
            'uses' => 'TargetController@syncUserGroups'
        ]);

        // TargetTemplateController
        $router->post('target_template.create', ['scopes' => 'target_template.create', 'uses' => 'TargetTemplateController@create']);
        $router->post('target_template.edit', ['scopes' => 'target_template.edit', 'uses' => 'TargetTemplateController@edit']);
        $router->get('target_template.getList', ['scopes' => 'target_template.getList', 'uses' => 'TargetTemplateController@getList']);

        // Target Geo
        $router->post('target_geo.create', ['scopes' => 'target_geo.create', 'uses' => 'TargetGeoController@create']);
        $router->post('target_geo.edit', ['scopes' => 'target_geo.edit', 'uses' => 'TargetGeoController@edit']);
        $router->post('target_geo.delete', ['scopes' => 'target_geo.delete', 'uses' => 'TargetGeoController@delete']);
        $router->post('target_geo.clone', ['scopes' => 'target_geo.clone', 'uses' => 'TargetGeoController@clone']);
        $router->get('target_geo.getList', [
            'scopes' => 'target_geo.getList',
            'uses' => 'TargetGeoController@getList'
        ]);
        $router->get('target_geo.getListByHash', [
            'scopes' => 'target_geo.getListByHash',
            'uses' => 'TargetGeoController@getListByHash'
        ]);
        $router->get('target_geo.getById', [
            'scopes' => 'target_geo.getById',
            'uses' => 'TargetGeoController@getById'
        ]);

        // Publisher Target Geo
        $router->get('publisher_target_geo.getList', [
            'scopes' => 'publisher_target_geo.getList',
            'uses' => 'PublisherTargetGeoController@getList'
        ]);
        $router->post('publisher_target_geo.create', [
            'scopes' => 'publisher_target_geo.create',
            'uses' => 'PublisherTargetGeoController@create'
        ]);
        $router->post('publisher_target_geo.edit', [
            'scopes' => 'publisher_target_geo.edit',
            'uses' => 'PublisherTargetGeoController@edit'
        ]);
        $router->delete('publisher_target_geo.delete', [
            'scopes' => 'publisher_target_geo.delete',
            'uses' => 'PublisherTargetGeoController@delete'
        ]);

        // Target Geo Rule
        $router->post('target_geo_rule.create', [
            'scopes' => 'target_geo_rule.create',
            'uses' => 'TargetGeoRuleController@create'
        ]);
        $router->post('target_geo_rule.edit', [
            'scopes' => 'target_geo_rule.edit',
            'uses' => 'TargetGeoRuleController@edit'
        ]);
        $router->post('target_geo_rule.delete', [
            'scopes' => 'target_geo_rule.delete',
            'uses' => 'TargetGeoRuleController@delete'
        ]);
        $router->get('target_geo_rule.getList', [
            'scopes' => 'target_geo_rule.getList',
            'uses' => 'TargetGeoRuleController@getList'
        ]);
        $router->post('target_geo_rule.editPriority', [
            'scopes' => 'target_geo_rule.editPriority',
            'uses' => 'TargetGeoRuleController@editPriority'
        ]);

        // Domain
        $router->post('domain.create', ['scopes' => 'domain.create', 'uses' => 'DomainController@create']);
        $router->post('domain.edit', ['scopes' => 'domain.edit', 'uses' => 'DomainController@edit']);
        $router->post('domain.delete', ['scopes' => 'domain.delete', 'uses' => 'DomainController@delete']);
        $router->get('domain.getList', ['scopes' => 'domain.getList', 'uses' => 'DomainController@getList']);
        $router->get('domain.getByHash', ['scopes' => 'domain.getByHash', 'uses' => 'DomainController@getByHash']);
        $router->post('domain.activate', ['scopes' => 'domain.activate', 'uses' => 'DomainController@activate']);
        $router->post('domain.deactivate', ['scopes' => 'domain.deactivate', 'uses' => 'DomainController@deactivate']);
        $router->get('domain.getRedirectDomain', [
            'scopes' => 'domain.getRedirectDomain',
            'uses' => 'DomainController@getRedirectDomain'
        ]);
        $router->post('domain.clearCache', [
            'scopes' => 'domain.clearCache',
            'uses' => 'DomainController@clearCache'
        ]);

        // Statistic
        $router->get('stat.getByHour', ['scopes' => 'stat.getByHour', 'uses' => 'StatisticController@getByHour']);
        $router->get('stat.getByDayHour', [
            'scopes' => 'stat.getByDayHour', 'uses' => 'StatisticController@getByDayHour'
        ]);
        $router->get('stat.getByDay', ['scopes' => 'stat.getByDay', 'uses' => 'StatisticController@getByDay']);
        $router->get('stat.getByFlow', ['scopes' => 'stat.getByFlow', 'uses' => 'StatisticController@getByFlow']);
        $router->get('stat.getByOffer', ['scopes' => 'stat.getByOffer', 'uses' => 'StatisticController@getByOffer']);
        $router->get('stat.getByGeo', ['scopes' => 'stat.getByGeo', 'uses' => 'StatisticController@getByGeo']);
        $router->get('stat.getByLanding', [
            'scopes' => 'stat.getByLanding',
            'uses' => 'StatisticController@getByLanding'
        ]);
        $router->get('stat.getByTransit', [
            'scopes' => 'stat.getByTransit',
            'uses' => 'StatisticController@getByTransit'
        ]);
        $router->get('stat.getByLead', [
            'scopes' => 'stat.getByLead',
            'uses' => 'StatisticController@getByLead'
        ]);
        $router->get('stat.getByRegion', [
            'scopes' => 'stat.getByRegion',
            'uses' => 'StatisticController@getByRegion'
        ]);
        $router->get('stat.getByCity', [
            'scopes' => 'stat.getByCity',
            'uses' => 'StatisticController@getByCity'
        ]);
        $router->get('stat.getByPublisher', [
            'scopes' => 'stat.getByPublisher',
            'uses' => 'StatisticController@getByPublisher'
        ]);
        $router->get('stat.getLeadInfoByHash', [
            'scopes' => 'stat.getLeadInfoByHash',
            'uses' => 'StatisticController@getLeadInfoByHash'
        ]);
        $router->get('stat.getOrderInfo', [
            'scopes' => 'stat.getOrderInfo',
            'uses' => 'StatisticController@getOrderInfo'
        ]);
        $router->get('stat.getByTargets', [
            'scopes' => 'stat.getByTargets',
            'uses' => 'StatisticController@getByTargets'
        ]);
        $router->get('stat.getByTargetGeo', [
            'scopes' => 'stat.getByTargetGeo',
            'uses' => 'StatisticController@getByTargetGeo'
        ]);

        $router->get('stat.getReport', [
            'scopes' => 'stat.getReport',
            'uses' => 'StatisticController@getReport'
        ]);

        $router->get('stat.getDeviceReport', [
            'scopes' => 'stat.getDeviceReport',
            'uses' => 'StatisticController@getDeviceReport'
        ]);

        // Currency
        $router->get('currency.getList', [
            'scopes' => 'currency.getList',
            'uses' => 'CurrencyController@getList'
        ]);

        //PostbackOutController
        $router->get('postbackout.getList', [
            'scopes' => 'postbackout.getList',
            'uses' => 'PostbackOutController@getList'
        ]);

        // LeadController
        $router->get('lead.getListOnHold', [
            'scopes' => 'lead.getListOnHold',
            'uses' => 'LeadController@getListOnHold'
        ]);
        $router->get('lead.getByHash', [
            'scopes' => 'lead.getByHash',
            'uses' => 'LeadController@getByHash'
        ]);
        $router->post('lead.integrate', [
            'scopes' => 'lead.integrate',
            'uses' => 'LeadController@integrate'
        ]);
        $router->post('lead.create', [
            'scopes' => 'lead.create',
            'uses' => 'LeadController@create'
        ]);
        $router->post('lead.bulkEdit', [
            'scopes' => 'lead.bulkEdit',
            'uses' => 'LeadController@bulkEdit'
        ]);
        $router->get('lead.buildReport', [
            'scopes' => 'lead.buildReport',
            'uses' => 'LeadController@buildReport'
        ]);
        $router->get('lead.getUncompleted', [
            'scopes' => 'lead.getUncompleted',
            'uses' => 'LeadController@getUncompleted'
        ]);
        $router->post('lead.completeByIds', [
            'scopes' => 'lead.completeByIds',
            'uses' => 'LeadController@completeByIds'
        ]);

        // BalanceTransactionController
        $router->post('balance_transaction.create', [
            'scopes' => 'balance_transaction.create',
            'uses' => 'BalanceTransactionController@create'
        ]);
        $router->post('balance_transaction.edit', [
            'scopes' => 'balance_transaction.edit',
            'uses' => 'BalanceTransactionController@edit'
        ]);
        $router->get('balance_transaction.getList', [
            'scopes' => 'balance_transaction.getList',
            'uses' => 'BalanceTransactionController@getList'
        ]);

        // Locale
        $router->get('locale.getList', ['scopes' => 'locale.getList', 'uses' => 'LocaleController@getList']);

        // Integration
        $router->post('integration.create', ['scopes' => 'integration.create', 'uses' => 'IntegrationController@create']);
        $router->post('integration.edit', ['scopes' => 'integration.edit', 'uses' => 'IntegrationController@edit']);
        $router->post('integration.delete', ['scopes' => 'integration.delete', 'uses' => 'IntegrationController@delete']);
        $router->get('integration.getById', ['scopes' => 'integration.getById', 'uses' => 'IntegrationController@getById']);
        $router->get('integration.getList', ['scopes' => 'integration.getList', 'uses' => 'IntegrationController@getList']);

        // Comebacker audio
        $router->post('comebacker_audio.create', [
            'scopes' => 'comebacker_audio.create',
            'uses' => 'ComebackerAudioController@create'
        ]);
        $router->post('comebacker_audio.edit', [
            'scopes' => 'comebacker_audio.edit',
            'uses' => 'ComebackerAudioController@edit'
        ]);
        $router->post('comebacker_audio.delete', [
            'scopes' => 'comebacker_audio.delete',
            'uses' => 'ComebackerAudioController@delete'
        ]);
        $router->get('comebacker_audio.getList', [
            'scopes' => 'comebacker_audio.getList',
            'uses' => 'ComebackerAudioController@getList'
        ]);

        // Deposit
        $router->post('deposit.create', [
            'scopes' => 'deposit.create',
            'uses' => 'DepositController@create'
        ]);
        $router->post('deposit.edit', [
            'scopes' => 'deposit.edit',
            'uses' => 'DepositController@edit'
        ]);
        $router->get('deposit.getList', [
            'scopes' => 'deposit.getList',
            'uses' => 'DepositController@getList'
        ]);

        // EmailIntegration
        $router->post('email_integration.create', [
            'scopes' => 'email_integration.create',
            'uses' => 'EmailIntegrationController@create'
        ]);
        $router->post('email_integration.edit', [
            'scopes' => 'email_integration.edit',
            'uses' => 'EmailIntegrationController@edit'
        ]);

        // EmailIntegration
        $router->post('email_integration.create', [
            'scopes' => 'email_integration.create',
            'uses' => 'EmailIntegrationController@create'
        ]);
        $router->post('email_integration.edit', [
            'scopes' => 'email_integration.edit',
            'uses' => 'EmailIntegrationController@edit'
        ]);

        // SmsIntegration
        $router->post('sms_integration.create', [
            'scopes' => 'sms_integration.create',
            'uses' => 'SmsIntegrationController@create'
        ]);
        $router->post('sms_integration.edit', [
            'scopes' => 'sms_integration.edit',
            'uses' => 'SmsIntegrationController@edit'
        ]);
        $router->post('sms_integration.delete', [
            'scopes' => 'sms_integration.delete',
            'uses' => 'SmsIntegrationController@delete'
        ]);
        $router->get('sms_integration.getList', [
            'scopes' => 'sms_integration.getList',
            'uses' => 'SmsIntegrationController@getList'
        ]);
        // EmailLog
        $router->get('email_log.show', [
            'scopes' => 'email_log.show',
            'uses' => 'EmailLogController@show'
        ]);

        // UserPersmission
        $router->get('user_permissions.getList', [
            'scopes' => 'user_permissions.getList',
            'uses' => 'UserPermissionsController@getList'
        ]);

        // UserUserPersmission
        $router->get('user_user_permissions.getForUser', [
            'scopes' => 'user_user_permissions.getForUser',
            'uses' => 'UserUserPermissionsController@getForUser'
        ]);
        $router->post('user_user_permissions.sync', [
            'scopes' => 'user_user_permissions.sync',
            'uses' => 'UserUserPermissionsController@sync'
        ]);

        // FailedJobs
        $router->get('failed_jobs.getList', [
            'scopes' => 'failed_jobs.getList',
            'uses' => 'FailedJobsController@getList'
        ]);
        $router->post('failed_jobs.retry', [
            'scopes' => 'failed_jobs.retry',
            'uses' => 'FailedJobsController@retry'
        ]);
        $router->post('failed_jobs.delete', [
            'scopes' => 'failed_jobs.delete',
            'uses' => 'FailedJobsController@delete'
        ]);

        // TargetGeoRuleStat
        $router->post('target_geo_rule_stat.reset', [
            'scopes' => 'target_geo_rule_stat.reset',
            'uses' => 'TargetGeoRuleStatController@reset'
        ]);

        // FileController
        $router->post('file.uploadImage', [
            'scopes' => 'file.uploadImage',
            'uses' => 'FileController@uploadImage'
        ]);
        $router->post('file.uploadAudio', [
            'scopes' => 'file.uploadAudio',
            'uses' => 'FileController@uploadAudio'
        ]);
        $router->get('file.show', [
            'uses' => 'FileController@show'
        ]);

        // FlowWidget
        $router->get('flow_widget.getList', [
            'scopes' => 'flow_widget.getList',
            'uses' => 'FlowWidgetController@getList'
        ]);

        // FlowFlowWidget
        $router->post('flow_flow_widget.create', [
            'scopes' => 'flow_flow_widget.create',
            'uses' => 'FlowFlowWidgetController@create'
        ]);
        $router->post('flow_flow_widget.edit', [
            'scopes' => 'flow_flow_widget.edit',
            'uses' => 'FlowFlowWidgetController@edit'
        ]);
        $router->delete('flow_flow_widget.delete', [
            'scopes' => 'flow_flow_widget.delete',
            'uses' => 'FlowFlowWidgetController@delete'
        ]);
        $router->get('flow_flow_widget.getCustomCodeList', [
            'scopes' => 'flow_flow_widget.getCustomCodeList',
            'uses' => 'FlowFlowWidgetController@getCustomCodeList'
        ]);
        $router->post('flow_flow_widget.moderate', [
            'scopes' => 'flow_flow_widget.moderate',
            'uses' => 'FlowFlowWidgetController@moderate'
        ]);

        // CloakSystem
        $router->get('cloak_system.getList', [
            'scopes' => 'cloak_system.getList',
            'uses' => 'CloakSystemController@getList'
        ]);

        // OfferLabel
        $router->get('offer_labels.getList', [
            'scopes' => 'offer_labels.getList',
            'uses' => 'OfferLabelController@getList'
        ]);

        // FlowGroup
        $router->post('flow_groups.create', [
            'scopes' => 'flow_groups.create',
            'uses' => 'FlowGroupsController@create'
        ]);
        $router->post('flow_groups.edit', [
            'scopes' => 'flow_groups.edit',
            'uses' => 'FlowGroupsController@edit'
        ]);
        $router->delete('flow_groups.delete', [
            'scopes' => 'flow_groups.delete',
            'uses' => 'FlowGroupsController@delete'
        ]);
        $router->get('flow_groups.getList', [
            'scopes' => 'flow_groups.getList',
            'uses' => 'FlowGroupsController@getList'
        ]);

        // PublisherStatisticsController
        $router->get('publisher_statistics.getList', [
            'scopes' => 'publisher_statistics.getList',
            'uses' => 'PublisherStatisticsController@getList'
        ]);

        // AdminDashboardController
        $router->get('admin_dashboard.getList', [
            'scopes' => 'admin_dashboard.getList',
            'uses' => 'AdminDashboardController@getList'
        ]);

        // CloakDomainPathsController
        $router->post('cloak_domain_paths.create', [
            'scopes' => 'cloak_domain_paths.create',
            'uses' => 'CloakDomainPathsController@create'
        ]);
        $router->post('cloak_domain_paths.edit', [
            'scopes' => 'cloak_domain_paths.edit',
            'uses' => 'CloakDomainPathsController@edit'
        ]);
        $router->delete('cloak_domain_paths.delete', [
            'scopes' => 'cloak_domain_paths.delete',
            'uses' => 'CloakDomainPathsController@delete'
        ]);
        $router->get('cloak_domain_paths.getList', [
            'scopes' => 'cloak_domain_paths.getList',
            'uses' => 'CloakDomainPathsController@getList'
        ]);
        $router->get('system.getLandingsPath', [
            'scopes' => 'system.getLandingsPath',
            'uses' => 'SystemController@getLandingsPath'
        ]);

        // UserGroupsController
        $router->post('user_groups.create', [
            'scopes' => 'user_groups.create',
            'uses' => 'UserGroupsController@create'
        ]);
        $router->post('user_groups.edit', [
            'scopes' => 'user_groups.edit',
            'uses' => 'UserGroupsController@edit'
        ]);
        $router->delete('user_groups.delete', [
            'scopes' => 'user_groups.delete',
            'uses' => 'UserGroupsController@delete'
        ]);
        $router->get('user_groups.getList', [
            'scopes' => 'user_groups.getList',
            'uses' => 'UserGroupsController@getList'
        ]);
        $router->get('user_groups.getById', [
            'scopes' => 'user_groups.getById',
            'uses' => 'UserGroupsController@getById'
        ]);
        // AccountController
        $router->get('account.getList', [
            'scopes' => 'account.getList',
            'uses' => 'AccountController@getList'
        ]);
        $router->post('account.create', [
            'scopes' => 'account.create',
            'uses' => 'AccountController@create'
        ]);
        $router->post('account.delete', [
            'scopes' => 'account.delete',
            'uses' => 'AccountController@delete'
        ]);
        // UserGroupTargetGeoController
        $router->get('user_group_target_geo.getList', [
            'scopes' => 'user_group_target_geo.getList',
            'uses' => 'UserGroupTargetGeoController@getList'
        ]);
        $router->post('user_group_target_geo.sync', [
            'scopes' => 'user_group_target_geo.sync',
            'uses' => 'UserGroupTargetGeoController@sync'
        ]);

        // ApiLogController
        $router->get('api_log.getList', [
            'scopes' => 'api_log.getList',
            'uses' => 'ApiLogController@getList'
        ]);
        $router->get('api_log.search', [
            'scopes' => 'api_log.search',
            'uses' => 'ApiLogController@search'
        ]);

        // AuthTokenController
        $router->get('auth_token.getList', [
            'scopes' => 'auth_token.getList',
            'uses' => 'AuthTokenController@getList'
        ]);
        $router->delete('auth_token.deleteByHash', [
            'scopes' => 'auth_token.deleteByHash',
            'uses' => 'AuthTokenController@deleteByHash'
        ]);
        $router->delete('auth_token.deleteExceptCurrenToken', [
            'scopes' => 'auth_token.deleteExceptCurrenToken',
            'uses' => 'AuthTokenController@deleteExceptCurrenToken'
        ]);

        //DomainReplacementController
        $router->post('domain_replacements.sync', [
            'scopes' => 'domain_replacements.sync',
            'uses' => 'DomainReplacementController@sync'
        ]);
        $router->get('domain_replacements.getList', [
            'scopes' => 'domain_replacements.getList',
            'uses' => 'DomainReplacementController@getList'
        ]);
        //BrowserController
        $router->get('browser.getList', [
            'scopes' => 'browser.getList',
            'uses' => 'BrowserController@getList'
        ]);
        //OsPlatformController
        $router->get('os_platform.getList', [
            'scopes' => 'os_platform.getList',
            'uses' => 'OsPlatformController@getList'
        ]);
        //TargetGeoIntegrationController
        $router->post('target_geo_integrations.create', [
            'scopes' => 'target_geo_integrations.create',
            'uses' => 'TargetGeoIntegrationController@create'
        ]);
        $router->post('target_geo_integrations.edit', [
            'scopes' => 'target_geo_integrations.edit',
            'uses' => 'TargetGeoIntegrationController@edit'
        ]);
    });
