<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Установка cors заголока, если это is_foreign_domain
 */
class LandingCors
{
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $headers['Access-Control-Allow-Origin'] = '*';

        return $response->withHeaders($headers);
    }
}
