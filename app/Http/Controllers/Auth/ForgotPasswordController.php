<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth as R;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    private $passwords;

    public function __construct(PasswordBroker $passwords)
    {
        $this->passwords = $passwords;
    }

    /**
     * @api {POST} /recoveryPasswordSend recoveryPasswordSend
     * @apiDescription Send email with link to recovery account password.
     *
     * @apiGroup Auth
     *
     * @apiParam {String} email Exising user's email in system.
     *
     * @apiSampleRequest /recoveryPasswordSend
     */
    public function sendResetLinkEmail(R\RecoveryPasswordSendRequest $request)
    {
        $this->passwords->sendResetLink($request->only('email'));

        return [
            'message' => trans('auth.on_recovery_password_send_success'),
            'status_code' => 200
        ];
    }
}