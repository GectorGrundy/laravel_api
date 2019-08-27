<?php
declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Models\AuthToken;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Route;
use Dingo\Api\Contract\Auth\Provider;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthServiceProvider implements Provider
{
    protected $auth;

    public function __construct(JWTAuth $auth)
    {
        $this->auth = $auth;
    }

    public function authenticate(Request $request, Route $route)
    {
        $token = $this->getToken($request);

        try {
            if (!$user = $this->auth->setToken($token)->authenticate()) {
                throw new UnauthorizedHttpException('Local', '#1 ' . trans('messages.unauthorized'));
            }

            try {
                /**
                 * @var AuthToken $auth_token
                 */
                $auth_token = AuthService::getTokenInfo($user, $token);
            } catch (ModelNotFoundException $e) {
                throw new UnauthorizedHttpException('Local', '#2 ' . trans('messages.unauthorized'));
            }

            $payload = $this->auth->setToken($token)->getPayload();

            $request->merge([
                'request_user' => [
                    'payload' => $payload,
                ],
                'auth_token' => $auth_token
            ]);

            $this->validateRouteScopes($route, $user);
            $this->validateCabinet($user);

            return $user;

        } catch (JWTException $exception) {
            throw new UnauthorizedHttpException('JWTAuth', $exception->getMessage(), $exception);
        }
    }

    /**
     * Get the JWT from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getToken(Request $request)
    {
        $token_param = config('jwt.name');

        if ($request->filled($token_param)) {
            return $request->input($token_param);
        }

        if ($request->hasCookie($token_param)) {
            return $request->cookie($token_param);
        }

        $this->validateAuthorizationHeader($request);

        return $this->parseAuthorizationHeader($request);
    }

    /**
     * Parse JWT from the authorization header.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    protected function parseAuthorizationHeader(Request $request)
    {
        return trim(str_ireplace($this->getAuthorizationMethod(), '', $request->header('authorization')));
    }

    /**
     * Get the providers authorization method.
     *
     * @return string
     */
    public function getAuthorizationMethod()
    {
        return 'bearer';
    }

    /**
     * Validate the requests authorization header for the provider.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return bool
     */
    public function validateAuthorizationHeader(Request $request)
    {
        if (Str::startsWith(
            strtolower($request->headers->get('authorization', '')),
            $this->getAuthorizationMethod()
        )) {
            return true;
        }

        throw new BadRequestHttpException;
    }

    /**
     * Validate a routes scopes.
     *
     * @param \Dingo\Api\Routing\Route $route
     * @param $user
     *
     * @return void
     */
    protected function validateRouteScopes(Route $route, $user): void
    {
        $permissions = $this->getPermissions($user['role']);

        $scopes = $route->scopes();
        if (empty($scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if (!\in_array($scope, $permissions)) {
                throw new AccessDeniedHttpException(trans('messages.unauthorized_scope', ['scope' => $scope]));
            }
        }
    }

    /**
     * Fetch the collection of site permissions.
     *
     * @param string $user_role
     * @return array
     */
    protected function getPermissions(string $user_role): array
    {
        return parse_ini_file(
            base_path('config/permissions/' . $user_role . '.ini'),
            true,
            INI_SCANNER_TYPED
        )['scopes'];
    }

    private function validateCabinet(User $user)
    {
        $clean_origin = str_replace(['http://', 'https://'], '', request()->headers->get('Origin'));

        if ($clean_origin === 'control.' . config('env.main_domain') && $user->role !== User::ADMINISTRATOR) {
            throw new AccessDeniedHttpException(trans('messages.unauthorized'));
        }

        if ($clean_origin === 'my.' . config('env.main_domain') && $user->role !== User::PUBLISHER) {
            throw new AccessDeniedHttpException(trans('messages.unauthorized'));
        }

        if ($clean_origin === 'office.' . config('env.main_domain') && $user->role !== User::ADVERTISER) {
            throw new AccessDeniedHttpException(trans('messages.unauthorized'));
        }

        if ($clean_origin === 'support.' . config('env.main_domain') && $user->role !== User::SUPPORT) {
            throw new AccessDeniedHttpException(trans('messages.unauthorized'));
        }
    }
}