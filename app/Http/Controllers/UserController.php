<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\Auth\UserPasswordRegenerated;
use App\Events\User\UserCreated;
use App\Models\Account;
use App\Models\AuthToken;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\User as R;
use App\Models\PublisherProfile;
use App\Models\AdministratorProfile;
use App\Models\AdvertiserProfile;
use App\Models\User;
use App\Models\UserStatisticSettings;
use Auth;
use Hashids;
use Illuminate\Database\Eloquent\Builder;

/**
 * @todo Сделать контроллеры для каждой роли и разнести методы
 */
class UserController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /user.createPublisher user.createPublisher
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {String} email Unique.
     * @apiParam {String{8..}} password
     * @apiParam {Number} [group_id=0]
     * @apiSampleRequest /user.createPublisher
     */
    public function createPublisher(R\CreatePublisherRequest $request)
    {
        $user = User::create([
            'role' => User::PUBLISHER,
            'nickname' => User::generateNickname($request->get('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'group_id' => $request->input('group_id'),
        ]);

        $user->publisher()->save(new PublisherProfile([]));

        $user = $user->fresh();
        $user->load('publisher');

        event(new UserCreated($user));

        return [
            'message' => trans('users.on_create_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /user.createAdministrator user.createAdministrator
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {String} email Unique.
     * @apiParam {String{8..}} password
     * @apiSampleRequest /user.createAdministrator
     */
    public function createAdministrator(R\CreateAdministratorRequest $request)
    {
        $administrator = User::create([
            'role' => User::ADMINISTRATOR,
            'nickname' => User::generateNickname($request->get('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        $administrator->administrator()->save(new AdministratorProfile([]));
        $administrator = $administrator->fresh();
        $administrator->load('administrator');

        event(new UserCreated($administrator));

        return [
            'message' => trans('users.on_create_success'),
            'response' => $administrator,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /user.createAdvertiser user.createAdvertiser
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {String} email Unique.
     * @apiParam {String{8..}} password
     * @apiParam {Number} [manager_id] Advertiser's manager ID.
     * @apiParam {String{..255}} info
     * @apiParam {String{..255}} full_name
     * @apiParam {String{..255}} skype
     * @apiParam {String{..255}} telegram
     * @apiParam {String{..16}} phone
     * @apiParam {String{..16}} whatsapp
     * @apiParam {String[]=1,3,5} [accounts] Currency ids to create accounts.
     * @apiSampleRequest /user.createAdvertiser
     */
    public function createAdvertiser(R\CreateAdvertiserRequest $request)
    {
        // Create user model
        $advertiser = User::create([
            'role' => User::ADVERTISER,
            'nickname' => User::generateNickname($request->get('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        // Create profile
        $inputs = $request->only([
            'full_name', 'skype', 'telegram', 'phone', 'whatsapp', 'info'
        ]);
        $inputs['manager_id'] = $request->input('manager_id') ?? 0;

        $profile = new AdvertiserProfile($inputs);
        $advertiser->advertiser()->save($profile);

        // Create accounts
        $accounts = $request->get('accounts', []);
        if (\count($accounts)) {
            foreach ($accounts as $currency_id) {
                Account::create([
                    'user_id' => $advertiser['id'],
                    'currency_id' => $currency_id,
                    'is_active' => 1,
                ]);
            }
        }

        $advertiser = $advertiser->fresh();

        User::$role = User::ADVERTISER;
        $advertiser->load('profile');

        event(new UserCreated($advertiser));

        return [
            'message' => trans('users.on_create_success'),
            'response' => $advertiser,
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /user.getList user.getList
     * @apiGroup User
     * @apiPermission admin
     * @apiPermission advertiser
     *
     * @apiParam {String[]=administrator,advertiser,publisher,suport} [role[]] Get users by specicified roles.
     * @apiParam {String[]} [hashes[]] Get users by these hashes
     * @apiParam {String{..255}} [search] Search string. Uses for search by email.
     * @apiParam {Number} [page]
     * @apiParam {Number{..50}} [per_page]
     *
     * @apiSampleRequest /user.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 50);
        $offset = paginationOffset($page, $per_page);

        $users = User::whereRoles(rejectEmpty($request->get('role', [])))
            ->search('email', $request->input('search', ''))
            ->when($request->input('hashes'), function (Builder $builder) use ($request) {
                $builder->whereIn('hash', $request->input('hashes', []));
            })
            ->offset($offset)
            ->limit($per_page)
            ->latest('id')
            ->get();

        return [
            'response' => $users,
            'status_code' => 200
        ];
    }

    /**
     * @api {GET} /user.getByHash user.getByHash
     * @apiGroup User
     * @apiPermission admin
     * @apiPermission publisher
     * @apiPermission advertiser
     * @apiPermission support
     *
     * @apiParam {String} user_hash
     * @apiSampleRequest /user.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $user_id = Hashids::decode($request->input('user_hash'))[0] ?? false;
        if (!$user_id) {
            return $this->response->error(trans('users.on_get_error'), 404);
        }

        $user = User::find($user_id);
        if (\is_null($user)) {
            return $this->response->error(trans('users.on_get_error'), 404);
        }

        User::$role = $user['role'];
        $user->load(['profile']);

        return [
            'response' => $user,
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /user.block user.block
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {Number} id Id of user with `active` status
     * @apiParam {String} reason_for_blocking
     * @apiSampleRequest /user.block
     */
    public function block(R\BlockRequest $request)
    {
        $user = User::find($request->input('id'));

        $user->update(['status' => User::LOCKED, 'reason_for_blocking' => $request->input('reason_for_blocking')]);

        $user = User::find($request->input('id'));

        return [
            'message' => trans('users.on_block_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /user.unlock user.unlock
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {Number} id Id of user with `locked` status
     * @apiSampleRequest /user.unlock
     */
    public function unlock(R\UnlockRequest $request)
    {
        $user = User::find($request->input('id'));

        $user->update(['status' => User::ACTIVE, 'reason_for_blocking' => '']);

        $user = User::find($request->input('id'));

        return [
            'message' => trans('users.on_unblock_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /user.changePassword user.changePassword
     * @apiGroup User
     * @apiPermission admin
     * @apiPermission advertiser
     * @apiPermission publisher
     *
     * @apiParam {String} password
     * @apiParam {String{8..}} new_password
     * @apiParam {String{8..}} new_password_confirmation Must be the same as `new_password` parameter
     * @apiSampleRequest /user.changePassword
     */
    public function changePassword(R\ChangePasswordRequest $request)
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        AuthToken::where('user_id', $user_id)
            ->where('hash', '!=', $request->input('auth_token')['hash'])
            ->where('admin_id', 0)
            ->delete();

        $user->update(['password' => $request->input('new_password')]);

        return [
            'message' => trans('users.on_change_password_success'),
            'response' => ['user_info' => $user],
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /user.getStatisticSettings user.getStatisticSettings
     * @apiGroup User
     * @apiPermission admin
     * @apiPermission advertiser
     * @apiPermission publisher
     * @apiSampleRequest /user.getStatisticSettings
     */
    public function getStatisticSettings()
    {
        $stat_settings = UserStatisticSettings::where('user_id', Auth::user()['id'])->first();

        return [
            'response' => $stat_settings,
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /user.updateStatisticSettings user.updateStatisticSettings
     * @apiGroup User
     * @apiPermission admin
     * @apiPermission advertiser
     * @apiPermission publisher
     *
     * @apiParam {Number=0,1} mark_roi
     * @apiParam {Array} columns
     * @apiSampleRequest /user.updateStatisticSettings
     */
    public function updateStatisticSettings(R\UpdateStatisticSettingsRequest $request)
    {
        $settings = UserStatisticSettings::firstOrNew(['user_id' => Auth::user()->id]);

        $settings->data = json_encode($request->only(['mark_roi', 'columns']));

        $settings->save();

        return [
            'message' => trans('users.on_update_settings_success'),
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /user.getBalance user.getBalance
     * @apiGroup User
     * @apiPermission publisher
     * @apiSampleRequest /user.getBalance
     */
    public function getBalance()
    {
        $data = PublisherProfile::select(
            'balance_rub', 'balance_usd', 'balance_eur', 'hold_rub', 'hold_usd', 'hold_eur'
        )
            ->where('user_id', Auth::id())
            ->first();

        return [
            'response' => $data,
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /user.regeneratePassword user.regeneratePassword
     * @apiDescription Regenerate password for publisher and support user role.
     * @apiGroup User
     * @apiPermission admin
     * @apiParam {Number} user_id
     * @apiSampleRequest /user.regeneratePassword
     */
    public function regeneratePassword(R\RegeneratePasswordRequest $request)
    {
        /**
         * @var User $user
         */
        $user = User::findOrFail((int)$request->input('user_id'));

        $new_password = getRandomCode(12);
        $user->update(['password' => $new_password]);

        event(new UserPasswordRegenerated($user, $new_password));

        return [
            'message' => trans('users.on_change_password_success'),
            'status_code' => 202
        ];
    }
}
