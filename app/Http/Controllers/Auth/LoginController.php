<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\{
    Account, FailedJob, Payment, Scopes\GlobalUserEnabledScope, User, UserPermission
};
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Auth as R;
use Illuminate\Foundation\Auth\ResetsPasswords;

class LoginController extends Controller
{
    use Helpers;
    use ResetsPasswords;

    /**
     * @api {POST} /login login
     * @apiGroup Auth
     * @apiParam {String} email
     * @apiParam {String} password
     * @apiParam {Number=0,1} remember
     * @apiSampleRequest /login
     */
    public function login(R\LoginRequest $request, AuthService $auth_service)
    {
        $request->merge([
            'email' => strtolower($request->input('email'))
        ]);

        if (!$auth_service->checkCredentials($request->input('email'), $request->input(['password']))) {
            return $this->response->errorUnauthorized(trans('auth.on_login_error'));
        }

        $user = User::getByEmail($request->input('email'));

        if ($user['status'] === User::LOCKED) {
            return $this->response->errorUnauthorized(trans('auth.banned', [
                'reason' => $user['reason_for_blocking'],
            ]));
        }

        $token = $auth_service->auth($user, (bool)$request->input('remember'));

        return [
            'message' => trans('auth.on_login_success'),
            'response' => [
                'dashboard_url' => getUserCabinetUrl($user->role),
                'token' => $token
            ],
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /auth.loginAsUser auth.loginAsUser
     * @apiDescription Login as user
     * @apiGroup Auth
     * @apiPermission admin
     * @apiPermission support
     * @apiParam {String} user_hash Hash of user to log in
     * @apiSampleRequest /auth.loginAsUser
     */
    public function loginAsUser(R\LoginAsUserRequest $request, AuthService $auth_service)
    {
        $current_user = Auth::user();

        $claims = [
            'parent_foreign_user_hash' => null,
            'foreign_user_hash' => $current_user['hash'],
        ];

        $auth_service->logout();

        $payload = $request->input('request_user')['payload'];
        if (isset($payload['foreign_user_hash']) && !empty($payload['foreign_user_hash'])) {
            $claims['parent_foreign_user_hash'] = $payload['foreign_user_hash'];
        }

        $user = User::where('hash', $request->input('user_hash'))->firstOrFail();

        $token = $auth_service->authAsUser($user, $claims);

        return [
            'response' => [
                'dashboard_url' => getUserCabinetUrl($user['role']),
                'token' => $token
            ],
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /auth.logoutAsUser auth.logoutAsUser
     * @apiDescription Logout from user cabinet and log in to own.
     * @apiGroup Auth
     * @apiPermission publisher
     * @apiPermission advertiser
     * @apiPermission support
     * @apiParam {String} foreign_user_hash Hash of user to logout
     * @apiSampleRequest /auth.logoutAsUser
     */
    public function logoutAsUser(R\LogoutAsUserRequest $request, AuthService $auth_service)
    {
        $user_to_log_in = User::withoutGlobalScope(GlobalUserEnabledScope::class)
            ->where('hash', $request->input('foreign_user_hash'))
            ->firstOrFail();

        $auth_service->logout();

        $claims = [
            'parent_foreign_user_hash' => null,
            'foreign_user_hash' => null,
        ];
        $payload = $request->input('request_user')['payload'];
        if (isset($payload['parent_foreign_user_hash']) && !empty($payload['parent_foreign_user_hash'])) {
            $claims['foreign_user_hash'] = $payload['parent_foreign_user_hash'];
        }

        $auth_service->authAsUser($user_to_log_in, $claims);

        return ['status_code' => 202];
    }

    /**
     * @api {POST} /logout logout
     * @apiGroup Auth
     * @apiPermission publisher
     * @apiPermission advertiser
     * @apiPermission support
     * @apiPermission admin
     * @apiSampleRequest /logout
     */
    public function logout(AuthService $auth_service)
    {
        $auth_service->logout();

        return ['status_code' => 202];
    }

    /**
     * @api {GET} /auth.getUser auth.getUser
     * @apiDescription Get user info by token for authentication purpose.
     * @apiGroup Auth
     *
     * @apiPermission publisher
     * @apiPermission advertiser
     * @apiPermission support
     * @apiPermission admin
     *
     * @apiSampleRequest /auth.getUser
     */
    public function getUser(Request $request, \Dingo\Api\Routing\Router $router)
    {
        User::$role = \Auth::user()['role'];

        $user = Auth::user()->load(['profile']);

        if ($user->isAdmin()) {
            $user['failed_jobs_count'] = FailedJob::count();
            $user['wait_payments_count'] = Payment::whereStatus(Payment::PENDING)->count();
        }

        if ($user->isPublisher()) {
            $user['permissions'] = UserPermission::getForUser($user);
        }

        if ($user->isAdvertiser()) {
            $user['accounts'] = Account::with(['currency'])->whereUser($user)->get();
        }

        return [
            'user' => $user,
            'foreign_user_hash' => $request->get('request_user')['payload']['foreign_user_hash'] ?? ''
        ];
    }
}
