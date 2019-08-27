<?php
declare(strict_types=1);

namespace Tests;

use App\Exceptions\User\UnknownUserRole;
use App\Models\User;
use App\Services\AuthTokenService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (isset($this->http_host)) {
            $_SERVER['HTTP_HOST'] = $this->http_host;
        }
    }

    public function auth(string $user_role)
    {
        $token_service = new AuthTokenService();

        switch ($user_role) {
            case User::PUBLISHER:
                $user = User::find(config('env.test_publisher_id'));
                break;

            case User::ADVERTISER:
                $user = User::find((int)config('env.test_advertiser_id'));
                break;

            case User::ADMINISTRATOR:
                $user = User::where('email', config('env.test_admin_email'))->first();
                break;

            case User::SUPPORT:
                $user = User::find(config('env.test_support_id'));
                break;

            default:
                throw new UnknownUserRole($user_role);
        }

        return [
            $token_service->createForUser($user),
            $user
        ];
    }

    public function headers(string $token)
    {
        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }
}