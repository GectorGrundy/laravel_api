<?php
declare(strict_types=1);

namespace App\Services;

use App\Events\Auth\Login;
use App\Events\Auth\Logout;
use App\Models\AuthToken;
use App\Models\User;

class AuthService
{
    /**
     * @var AuthTokenService
     */
    private $token_service;

    public function __construct(AuthTokenService $auth_token_service)
    {
        $this->token_service = $auth_token_service;
    }

    /**
     * Поиск пользователя по email & password
     *
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function checkCredentials(string $email, string $password): bool
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        return \Auth::guard('web')->once($credentials);
    }

    /**
     * Аутентификация пользователя
     *
     * @param User $user
     * @param bool $remember
     * @return string
     */
    public function auth(User $user, bool $remember = false): string
    {
        $token = $this->token_service->createForUser($user, $remember);

        event(new Login($user));

        return $token;
    }

    /**
     * Аутентификация пользователя
     *
     * @param User $user
     * @param array $claims
     * @return string
     */
    public function authAsUser(User $user, array $claims = []): string
    {
        return $this->token_service->createForUser($user, true, $claims);
    }

    public function logout(): void
    {
        $this->token_service->remove(request()->input('auth_token'));
    }

    public static function getTokenInfo(User $user, string $token)
    {
        return AuthToken::whereToken($token)->whereUserId($user['id'])->firstOrFail();
    }
}
