<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\GoDataContainer;
use App\Services\CloakingService;
use App\Http\Requests\Go\GoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Cookie;

class VisitorCookie
{
    private $cookie_domain;
    private $go_request;
    private $data_container;

    public function __construct(GoRequest $go_request, GoDataContainer $data_container)
    {
        $this->go_request = $go_request;
        $this->data_container = $data_container;
    }

    public function handle(Request $request, \Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $this->cookie_domain = $this->go_request->getCookieDomain();

        $this->setSid($response);
        $this->setSafepage($request);

        return $response;
    }

    /**
     * @param Response $response
     */
    private function setSid($response)
    {
        $response->withCookie(
            cookie(
                config('session.visitor_session'),
                $this->data_container->getVisitor()['s_id'],
                config('session.visitor_session_ttl'),
                '/',
                $this->cookie_domain
            )
        );
    }

    private function setSafepage(Request $request): void
    {
        $flow = $this->data_container->getFlow();
        $current_domain = $this->data_container->getCurrentDomain();

        if (is_null($flow)
            || is_null($this->data_container->isSafepage())
            || $request->hasCookie(CloakingService::COOKIE_NAME)
        ) {
            return;
        }

        if ($current_domain->isCloaked()) {
            $is_cache_result = (bool)$this->data_container->getCloakDomainPath()['is_cache_result'];
        } else {
            $is_cache_result = (bool)$flow->cloak['is_cache_result'];
        }

        if ($is_cache_result && !$request->hasCookie(CloakingService::COOKIE_NAME)) {
            Cookie::queue(
                Cookie::make(
                    CloakingService::COOKIE_NAME,
                    var_export($this->data_container->isSafepage(), true),
                    CloakingService::COOKIE_TTL,
                    '/',
                    $this->cookie_domain
                )
            );
        } elseif (!$is_cache_result && $request->hasCookie(CloakingService::COOKIE_NAME)) {
            Cookie::queue(
                Cookie::forget(
                    CloakingService::COOKIE_NAME,
                    '/',
                    $this->cookie_domain
                )
            );
        }
    }
}
