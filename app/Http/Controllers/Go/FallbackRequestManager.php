<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Models\{
    Landing, Transit
};
use App\Http\GoDataContainer;
use App\Events\Go\SiteVisited;
use App\Http\Controllers\Controller;
use App\Http\GoEntityResolvers\BotResolver;
use App\Services\CloakingService;

class FallbackRequestManager extends Controller
{
    private $bot_resolver;
    private $data_container;
    private $path_cloak_service;

    public function __construct(
        BotResolver $bot_resolver, GoDataContainer $data_container, CloakingService $path_cloak_service
    )
    {
        $this->bot_resolver = $bot_resolver;
        $this->data_container = $data_container;
        $this->path_cloak_service = $path_cloak_service;
    }

    public function __invoke($path = 'index.html')
    {
        $current_domain = $this->data_container->getCurrentDomain();
        $flow = $this->data_container->getFlow();
        $visitor = $this->data_container->getVisitor();
        $cloak_path = $this->data_container->getCloakDomainPath();

        // Show donor page
        if ($current_domain->isCloaked() && \is_null($cloak_path)) {
            return $this->path_cloak_service->showPage($path, $current_domain);
        }

        // Проверка на бота
        $this->bot_resolver->resolve($flow, $visitor);

        // Traffback
        if (!empty($flow['tb_url']) && $this->data_container->isFallbackTargetGeo()) {
            $response = response()->redirectTo($flow['tb_url']);
            $this->data_container->setIsTraffback(true);

        } elseif ($current_domain->isCloaked()) {

            if (!\is_null($cloak_path) && $this->path_cloak_service->isSafePage($cloak_path)) {
                $response = $this->path_cloak_service->showPage($path, $current_domain);
                $this->data_container->setIsSafepage(true);
            } else {
                $response = $this->showSite($path);
            }

        } else {
            $response = $this->showSite($path);
        }

        event(new SiteVisited($this->data_container));

        return $response;
    }

    private function showSite(string $path)
    {
        $site = $this->data_container->getSite();
        $response = null;

        if ($site instanceof Transit) {
            return app(ShowTransit::class)($path);
        }

        if ($site instanceof Landing) {
            return app(ShowLanding::class)();
        }

        throw new \BadMethodCallException();
    }
}
