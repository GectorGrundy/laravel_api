<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Domain;
use App\Http\GoDataContainer;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CurrentDomainInfo
{
    private $data_container;

    public function __construct(GoDataContainer $data_container)
    {
        $this->data_container = $data_container;
    }

    public function handle(Request $request, \Closure $next)
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return abort(404);
        }

        try {
            $current_domain = Domain::getByDomain($_SERVER['HTTP_HOST'], ['entity']);

            if ($current_domain->isRedirect()) {
                return abort(404);
            }

            $this->data_container->setCurrentDomain($current_domain);

        } catch (ModelNotFoundException $e) {
            return abort(404);
        }

        return $next($request);
    }
}
