<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Events\Dispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Asm89\Stack\CorsService;

class HandleCors
{
    /** @var CorsService $cors */
    protected $cors;

    /** @var Dispatcher $events */
    protected $events;

    public function __construct(CorsService $cors, Dispatcher $events)
    {
        $this->cors = $cors;
        $this->events = $events;
    }

    /**
     * Handle an incoming request. Based on Asm89\Stack\Cors by asm89
     * @see https://github.com/asm89/stack-cors/blob/master/src/Asm89/Stack/Cors.php
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next)
    {
        if (!$this->isCabinetRequest($request)) {
            return $next($request);
        }

        if (!$this->cors->isCorsRequest($request)) {
            return $next($request);
        }

        if ($this->cors->isPreflightRequest($request)) {
            return $this->cors->handlePreflightRequest($request);
        }

        if (!$this->cors->isActualRequestAllowed($request)) {
            return new Response('Not allowed.', 403);
        }

        $this->events->listen('kernel.handled', function (Request $request, Response $response) {
            $this->addHeaders($request, $response);
        });

        $response = $next($request);

        return $this->addHeaders($request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    protected function addHeaders(Request $request, Response $response)
    {
        // Prevent double checking
        if (!$response->headers->has('Access-Control-Allow-Origin')) {
            $response = $this->cors->addActualRequestHeaders($response, $request);
        }

        return $response;
    }

    private function isCabinetRequest(Request $request)
    {
        $allowed_origins = config('cors.allowedOrigins');
        $origin = $request->header('Origin');

        return in_array($origin, $allowed_origins, true);
    }
}
