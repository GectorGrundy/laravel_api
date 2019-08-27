<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\GoDataContainer;
use Closure;
use App\Models\Locale;
use Illuminate\Http\Request;

/**
 * Установка локали приложения в зависимости от текущей локали лендинга/прелендинга
 */
class LandingLocale
{
    /**
     * @var Locale
     */
    private $locale;
    /**
     * @var GoDataContainer
     */
    private $data_container;

    public function __construct(Locale $locale, GoDataContainer $data_container)
    {
        $this->locale = $locale;
        $this->data_container = $data_container;
    }

    public function handle(Request $request, Closure $next)
    {
        // Its donor page of new cloaking
        if ($this->data_container->getCurrentDomain()->isCloaked() && is_null($this->data_container->getFlow())) {
            return $next($request);
        }

        $locale = $this->locale->getById($this->data_container->getSite()['locale_id']);
        app()->setLocale($locale['code']);

        $this->data_container->setLocale($locale);

        return $next($request);
    }
}
