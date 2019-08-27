<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuthToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthTokenLastActivity
{
    private static $excepted_api_methods = ['auth.loginAsUser', 'auth.logoutAsUser', 'file.show', 'logout'];

    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Response $response
         */
        $response = $next($request);

        $api_method = substr($request->getPathInfo(), 1);

        if (in_array($api_method, self::$excepted_api_methods)) {
            return $response;
        }

        /**
         * @var AuthToken $auth_token
         */
        $auth_token = $request->input('auth_token');
        $auth_token->updateLastActivity();

        return $response;
    }
}
