<?php
declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\AuthTokenLastActivity;
use App\Http\Middleware\CreateApiLog;
use App\Http\Middleware\CurrentDomainInfo;
use App\Http\Middleware\EntitiesInfo;
use App\Http\Middleware\GoFallbackLocale;
use App\Http\Middleware\LandingCors;
use App\Http\Middleware\LandingLocale;
use App\Http\Middleware\NginxForStatic;
use App\Http\Middleware\PostbackinCreateLog;
use App\Http\Middleware\PublisherApiAuth;
use App\Http\Middleware\RequestParameters;
use App\Http\Middleware\VisitorCookie;
use App\Http\Middleware\VisitorInfo;
use App\Http\Middleware\VisitorTargetGeo;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
//        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\Timezones::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ],

        'cookie' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        ],

        'session' => [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'api.locale' => \App\Http\Middleware\ApiLocale::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'current_domain_info' => CurrentDomainInfo::class,
        'domain.site.flow.offer.info' => EntitiesInfo::class,
        'visitor.cookie' => VisitorCookie::class,
        'visitor.info' => VisitorInfo::class,
        'visitor.target_geo' => VisitorTargetGeo::class,
        'landing.locale' => LandingLocale::class,
        'landing.cors' => LandingCors::class,
        'api.log' => CreateApiLog::class,
        'auth_token.last_activity' => AuthTokenLastActivity::class,
        'postbackin.log' => PostbackinCreateLog::class,
        'nginx_for_static' => NginxForStatic::class,
        'request_parameters' => RequestParameters::class,
        'publisher.api.auth' => PublisherApiAuth::class,
        'go_fallback_locale' => GoFallbackLocale::class,
        'trusted_proxies' => \Fideloper\Proxy\TrustProxies::class,
    ];
}
