<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Auth as R;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Models\{
    User, PublisherProfile, UserGroup
};
use Illuminate\Http\Request;
use Illuminate\Mail\Message;

class RegistrationController extends Controller
{
    use Helpers;
    use ResetsPasswords;

    private const ADMIN_EMAILS = ['avs@hl-web.biz', 'itstokey@gmail.com', 'vicentiy@gmail.com'];
    private const TEST_ADMIN_EMAILS = ['ionlineua@gmail.com'];

    private $auth_service;

    public function __construct(AuthService $auth_service)
    {
        $this->auth_service = $auth_service;
    }

    /**
     * @api {POST} /registration registration
     * @apiGroup Auth
     * @apiParam {String=advertiser,publisher} user_role
     * @apiParam {String} email Unique email in the system
     * @apiParam {String} [password]  Required if `user_role`=`publisher`
     * @apiParam {String} [g-recaptcha-response]  Required if `user_role`=`publisher`
     * @apiParam {String} [phone]     Required if `user_role`=`advertiser`
     * @apiParam {String} [contacts]  Required if `user_role`=`advertiser`
     * @apiParam {String} [geo]       Required if `user_role`=`advertiser`
     * @apiSampleRequest /registration
     */
    public function register(R\RegistrationRequest $request)
    {
        if ($request->input('user_role') === User::ADVERTISER) {
            return $this->processAdvertiser($request);
        }

        return $this->processPublisher($request);
    }

    private function processPublisher(Request $request)
    {
        $user = User::create(array_merge($request->all(), [
            'role' => User::PUBLISHER,
            'group_id' => UserGroup::DEFAULT_ID,
        ]));

        PublisherProfile::create(['user_id' => $user->id]);

        $this->auth_service->auth($user, false);

        event(new UserRegistered($user, $request->all()));

        return $this->response->accepted(null, [
            'message' => trans('publishers.on_create_success'),
            'response' => $user,
            'status_code' => 202
        ]);
    }

    private function processAdvertiser(Request $request)
    {
        foreach (self::ADMIN_EMAILS as $email) {

            \Mail::send(
                'emails.registration.advertiser',
                ['request' => $request->all()],
                function (Message $m) use ($email) {
                    $m->from(config('env.mail_from'), config('env.mail_sender'))
                        ->to($email)
                        ->subject('Заявка на регистрацию рекламодателя');
                });
        }

        return $this->response->accepted(null, [
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /promoQuestion promoQuestion
     * @apiGroup Auth
     * @apiPermission unauthorized
     * @apiParam {String} name
     * @apiParam {String} email
     * @apiParam {String} message
     * @apiSampleRequest /promoQuestion
     */
    public function promoQuestion(R\PromoQuestionRequest $request)
    {
        foreach (self::TEST_ADMIN_EMAILS as $email) {
            \Mail::send(
                'emails.registration.question',
                ['request' => $request->all()],
                function (Message $m) use ($email) {
                    $m->from(config('env.mail_from'), config('env.mail_sender'))
                        ->to($email)
                        ->subject('Вопрос с главной страницы');
                });
        }

        return $this->response->accepted(null, [
            'status_code' => 202
        ]);
    }
}
