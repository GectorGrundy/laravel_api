<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuthToken;
use App\Models\User;
use Carbon\Carbon;
use Cookie;
use Tymon\JWTAuth\PayloadFactory;

/**
 * Класс для генерации/деактивации токена
 */
class AuthTokenService
{
    public function createForUser(User $user, bool $remember = false, array $custom_claims = []): string
    {
        $token = $this->generate($user, $remember, $custom_claims);

        $this->setCookie($token, $remember);

        $this->insertAuthToken($user, $token, $custom_claims);

        return $token;
    }

    public function generate(User $user, bool $remember, array $custom_claims = []): string
    {
        $ip = request()->get('user_ip', request()->ip());

        $custom_claims['user_hash'] = $user['hash'];
        $custom_claims['ip'] = $ip;
        $custom_claims['type'] = 'token';

        // Generate long term ttl for token
        if ($remember) {
            /**
             * @var PayloadFactory $payload_factory
             */
            $payload_factory = app('tymon.jwt.payload.factory');
            $payload_factory->setTTL(config('jwt.remember_ttl'));
        }

        return \JWTAuth::fromUser($user, $custom_claims);
    }

    public function remove(AuthToken $token): void
    {
        $token->delete();

        Cookie::queue(Cookie::forget(config('jwt.name')));
    }

    private function setCookie(string $token, bool $remember): void
    {
        $ttl = $remember ? config('jwt.remember_ttl') : config('jwt.ttl');

        Cookie::queue(Cookie::make(
            config('jwt.name'), $token, $ttl, null, null, false, false
        ));
    }

    private function insertAuthToken(User $user, string $token, array $custom_claims = []): void
    {
        $admin_id = 0;
        if (isset($custom_claims['foreign_user_hash'])) {
            $admin_id = \Hashids::decode($custom_claims['foreign_user_hash'])[0] ?? 0;
        }

        AuthToken::create([
            'token' => $token,
            'user_id' => $user['id'],
            'admin_id' => $admin_id,
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'last_activity' => Carbon::now()->toDateTimeString()
        ]);
    }
}