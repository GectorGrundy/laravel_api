<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Lang;

class GoFallbackLocale
{
    public function handle($request, Closure $next)
    {
        Lang::setFallback('en');

        return $next($request);
    }
}
