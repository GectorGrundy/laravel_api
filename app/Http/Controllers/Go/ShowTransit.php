<?php
declare(strict_types=1);

namespace App\Http\Controllers\Go;

use App\Http\GoDataContainer;
use App\Classes\{
    LandingHandler, TransitHandler
};
use App\Http\Requests\Go\GoRequest;
use App\Http\Controllers\Controller;
use App\Services\CloakingService;
use App\Services\LandingUrlResolver;

/**
 * Отображение прелендинга
 */
class ShowTransit extends Controller
{
    private $landing_handler;
    private $transit_handler;
    private $go_request;
    private $data_container;

    public function __construct(
        LandingHandler $landing_handler, TransitHandler $transit_handler, GoRequest $go_request,
        GoDataContainer $data_container
    )
    {
        $this->landing_handler = $landing_handler;
        $this->transit_handler = $transit_handler;
        $this->go_request = $go_request;
        $this->data_container = $data_container;
    }

    public function __invoke(string $path)
    {
        $current_domain = $this->data_container->getCurrentDomain();

        $landing = $this->landing_handler->getLandingForShow();

        $url_params = $this->go_request->getRequiredUrlParams();
        $url_params[CloakingService::FOREIGN_PARAM] = $landing['hash'];

        if (!\is_null($this->data_container->getCloakDomainPath())) {
            $url_params[CloakingService::PATH_PARAM] = $this->data_container->getCloakDomainPath()['hash'];
        }

        $landing_target_blank = true;

        if ($current_domain->isCloaked()) {
            $landing_target_blank = false;
        }

        $landing_url = LandingUrlResolver::getUrl($url_params);

        $transit_html = $this->transit_handler->getTransitHtml($landing_url, $landing_target_blank);

        return response($transit_html);
    }
}
