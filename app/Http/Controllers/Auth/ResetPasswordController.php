<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use Auth;
use App\Http\Requests\Auth as R;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;


    /**
     * @api {POST} /passwordReset passwordReset
     * @apiDescription Send email with link to recovery account password.
     *
     * @apiGroup Auth
     *
     * @apiParam {String} email Exising user's email in system
     * @apiParam {String{8..}} password New password
     * @apiParam {String} token Secret string which was sent in email
     *
     * @apiSampleRequest /passwordReset
     */
    public function passwordReset(R\PasswordResetRequest $request)
    {
        $request->merge(['password_confirmation' => $request->input('password')]);

        return $this->reset($request);
    }

    private function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        if ($response !== Password::PASSWORD_RESET) {
            return $this->sendResetFailedResponse();
        }

        // Авторизуем пользователя
        $token = app(AuthService::class)->auth(Auth::user());

        return $this->sendSuccessResetResponse($token);
    }

    private function sendSuccessResetResponse($token)
    {
        return [
            'message' => trans('auth.on_recovery_password_success'),
            'response' => [
                'redirect_url' => getUserCabinetUrl(Auth::user()->role),
                'token' => $token
            ],
            'status_code' => 200
        ];
    }

    private function sendResetFailedResponse()
    {
        return [
            'message' => trans('auth.on_recovery_password_validate_error'),
            'errors' => [
                'password' => [trans('auth.token.exists')]
            ],
            'status_code' => 422
        ];
    }
}