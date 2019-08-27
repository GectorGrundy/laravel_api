<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Classes\LandingHandler;
use App\Classes\TransitHandler;
use App\Exceptions\Flow\UnknownFlowIdentifier;
use App\Exceptions\Landing\CouldntDetectSite;
use App\Exceptions\Visitor\IncorrectCacheDataException;
use App\Http\GoDataContainer;
use App\Models\{
    CloakDomainPath, Domain, Flow, Landing, Transit, Visitor
};
use App\Services\CloakingService;
use Illuminate\Http\Request;
use App\Exceptions\Hashids\NotDecodedHashException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router;

class EntitiesInfo
{
    /**
     * @var \Illuminate\Routing\Router
     */
    private $router;
    /**
     * @var GoDataContainer
     */
    private $data_container;
    /**
     * @var LandingHandler
     */
    private $landing_handler;
    /**
     * @var TransitHandler
     */
    private $transit_handler;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Flow
     */
    private $flow;

    public function __construct(
        GoDataContainer $data_container, LandingHandler $landing_handler, TransitHandler $transit_handler, Flow $flow,
        Router $router
    )
    {
        $this->router = app('router');
        $this->data_container = $data_container;
        $this->landing_handler = $landing_handler;
        $this->transit_handler = $transit_handler;
        $this->flow = $flow;
        $this->router = $router;
    }

    public function handle(Request $request, \Closure $next)
    {
        $this->request = $request;
        $current_domain = $this->data_container->getCurrentDomain();

        $this->resolveFlow();
        if (\is_null($this->data_container->getFlow())) {
            if ($current_domain->isCloaked()) {
                return $next($request);
            }
            return abort(404);
        }

        $this->resolveOffer();

        $this->resolveSite();

        // @todo Вернуть валидацию после определения судьбы мобильной ротации потока
//        if (!$this->resolvedFlowHasSite()) {
//            return $next($request);
//        }

        return $next($request);
    }

    private function resolveFlow(): void
    {
        $current_domain = $this->data_container->getCurrentDomain();

        try {
            if ($current_domain->isCloaked() && $this->isFallbackRoute()) {
                $this->resolveCloakDomainPath();
            }

            // Tds route
            if ($this->isTdsRoute()) {
                $this->data_container->setFlow($this->getFlowForTds());
                return;
            }

            // URL parameter
            if ($this->request->filled('flow_hash')) {
                $this->data_container->setFlow($this->flow->getByHash($this->request->get('flow_hash')));
                return;
            }

            // Fallback of domain
            $current_domain = $this->data_container->getCurrentDomain();
            if ($current_domain->isParked()) {

                // New cloaking
                if ($current_domain->isCloaked() && $this->isFallbackRoute()) {

                    $cloak_domain_path = $this->data_container->getCloakDomainPath();
                    if (!\is_null($cloak_domain_path)) {
                        $this->data_container->setFlow($this->flow->getById($cloak_domain_path->flow_id));
                    }
                    return;
                }

                // Old cloaking
                $this->data_container->setFlow($this->flow->getById((int)$current_domain->fallback_flow_id));
                return;
            }


            if ($this->request->filled(CloakingService::FOREIGN_PARAM)) {
                $this->resolveSiteByHash();
                $this->resolveFlowByCacheOrFallback();

            } elseif ($current_domain->isParked() || $this->isTdsRoute() || $current_domain->isTds()) {
                $this->resolveFlowByCacheOrFallback();
                $this->resolveSiteByFlowSettings();

            } else {
                $this->resolveSiteByCurrentDomain();
                $this->resolveFlowByCacheOrFallback();
            }

        } catch (NotDecodedHashException | ModelNotFoundException $e) {
            throw new UnknownFlowIdentifier();
        }
    }

    public function resolveSite(): void
    {
        if (!\is_null($this->data_container->getSite())) {
            return;
        }

        $current_domain = $this->data_container->getCurrentDomain();

        if ($this->request->filled(CloakingService::FOREIGN_PARAM)) {
            $this->resolveSiteByHash();

        } elseif ($current_domain->isParked() || $this->isTdsRoute() || $current_domain->isTds()) {
            $this->resolveSiteByFlowSettings();

        } else {
            $this->resolveSiteByCurrentDomain();
        }
    }

    private function resolveFlowByCacheOrFallback()
    {
        $visitor = $this->data_container->getVisitor();
        $site = $this->data_container->getSite();

        if (\is_null($site)) {
            throw new CouldntDetectSite();
        }

        if ($visitor['is_fallback']) {
            $flow = Flow::getFallbackOrCreate($site->offer, $site->target);
        } else {
            try {
                $flow_hash = (new Visitor())->getLastShowedFlowHashInOffer($visitor['info'], $site['offer_id']);
                $flow = $this->flow->getByHash($flow_hash);
            } catch (IncorrectCacheDataException $e) {
                $flow = Flow::getFallbackOrCreate($site->offer, $site->target);
            }
        }

        $this->data_container->setFlow($flow);
    }

    private function getNormalizedPath(): string
    {
        if (str_contains($this->request->getRequestUri(), 'index.html')) {
            return '/';
        }
        return urldecode($this->request->getPathInfo());
    }

    private function isFallbackRoute(): bool
    {
        return $this->router->getCurrentRoute()->getName() === 'fallback';
    }

    private function resolvedFlowHasSite()
    {
        $flow = $this->data_container->getFlow();
        $current_domain = $this->data_container->getCurrentDomain();
        if ($flow->isFallbackPublisher() || $current_domain->isTds()) {
            return true;
        }

        $visitor = $this->data_container->getVisitor();
        $site = $this->data_container->getSite();
        $flow = $this->data_container->getFlow();

        if ($site instanceof Transit) {
            $entity_ids = $flow->transits->pluck('id');
        } else {
            $entity_ids = $flow->landings->pluck('id');
        }

        return $entity_ids->contains($site->id);
    }

    private function resolveSiteByCurrentDomain()
    {
        $current_domain = $this->data_container->getCurrentDomain();

        if ($current_domain->isTds()) {
            return;
        } elseif ($current_domain->isTransit() && !$current_domain->isService()) {
            $this->data_container->setTransit($current_domain->entity);

        } elseif ($current_domain->isLanding() && !$current_domain->isService()) {
            $this->data_container->setLanding($current_domain->entity);

        } else {
            return abort(404);
        }
    }

    private function resolveCloakDomainPath(): void
    {
        if ($this->request->filled(CloakingService::PATH_PARAM)) {

            $path_hash = $this->request->input(CloakingService::PATH_PARAM);
            $path = CloakDomainPath::where('hash', $path_hash)->first();

        } else {
            $path = CloakDomainPath::whereDomain($this->data_container->getCurrentDomain())
                ->wherePath($this->getNormalizedPath())
                ->first();
        }

        $this->data_container->setCloakDomainPath($path);
    }

    private function resolveSiteByHash()
    {
        $hash = $this->request->get(CloakingService::FOREIGN_PARAM);

        try {
            $site_type = $this->request->get(CloakingService::FOREIGN_LANDING_TYPE);

            if ($site_type === Domain::TRANSIT_ENTITY_TYPE) {
                $entity = (new Transit())->getByHash($hash);
                $this->data_container->setTransit($entity);
            } else {
                $entity = (new Landing())->getByHash($hash);
                $this->data_container->setLanding($entity);
            }
        } catch (ModelNotFoundException | NotDecodedHashException $e) {
            return abort(404);
        }
    }

    public function resolveSiteByFlowSettings(): void
    {
        $flow = $this->data_container->getFlow();
        if (\is_null($flow)) {
            return;
        }

        try {
            $transit = $this->transit_handler->getTransitForShow();
            $this->data_container->setTransit($transit);

        } catch (ModelNotFoundException $e) {
            $landing = $this->landing_handler->getLandingForShow();
            $this->data_container->setLanding($landing);
        }
    }

    private function isTdsRoute(): bool
    {
        $route_as = $this->router->getCurrentRoute()->getAction()['as'] ?? '';

        return $route_as === 'tds';
    }

    private function getFlowForTds()
    {
        try {
            $flow_hash = $this->router->getCurrentRoute()->parameters()['flow_hash'];
            return $this->flow->getByHash($flow_hash);

        } catch (NotDecodedHashException | ModelNotFoundException $e) {
            throw new UnknownFlowIdentifier();
        }
    }

    private function resolveOffer()
    {
        $flow = $this->data_container->getFlow();
        if (!\is_null($flow)) {
            $this->data_container->setOffer($flow->load('offer')->offer);
        }
    }
}
