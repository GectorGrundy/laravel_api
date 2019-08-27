<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use App\Events\User\UserCreated;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Manager as R;
use App\Models\{
    Manager, ManagerProfile, User
};

class ManagerController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /manager.create manager.create
     * @apiGroup Manager
     * @apiPermission admin
     * @apiParam {String} email Unique email for user.
     * @apiParam {String} password
     * @apiSampleRequest /manager.create
     */
    public function create(R\CreateRequest $request)
    {
        $user = User::create([
            'role' => User::MANAGER,
            'nickname' => User::generateNickname($request->get('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        $user->manager()->save(new ManagerProfile([]));

        $user = $user->fresh();
        $user->load('manager');

        event(new UserCreated($user));

        return [
            'message' => trans('users.on_create_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /manager.changeProfile manager.changeProfile
     * @apiGroup Manager
     * @apiPermission manager
     * @apiPermission admin
     * @apiParam {Number} [user_id] manager ID. Required for admin
     * @apiParam {String{..255}} full_name
     * @apiParam {String{..255}} skype
     * @apiParam {String{..255}} telegram
     * @apiParam {String{..16}} phone
     * @apiSampleRequest /manager.changeProfile
     */
    public function changeProfile(R\ChangeProfileRequest $request)
    {
        $user = Auth::user();
        $user_id = Auth::user()['id'];
        $inputs = $request->only(['full_name', 'skype', 'telegram', 'phone']);

        if ($user->isAdmin()) {
            $user_id = \Hashids::decode($request->input('user_hash'))[0];
        }

        $profile = ManagerProfile::where('user_id', $user_id)->firstOrFail();
        $profile->update($inputs);

        return [
            'message' => trans('users.on_change_profile_success'),
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /manager.getList manager.getList
     * @apiGroup Support
     * @apiPermission admin
     *
     * @apiParam {Number} [page=1]
     * @apiParam {Number{..100}} [per_page=25]
     * @apiParam {String[]=profile} [with[]]
     *
     * @apiSampleRequest /manager.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 25);
        $offset = paginationOffset($page, $per_page);

        $query = Manager::search($request->input('search_field'), $request->input('search'));

        $total_leads = clone $query;
        $total = (int)($total_leads->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        $managers = $query
            ->with($request->input('with', []))
            ->offset($offset)
            ->limit($per_page)
            ->latest('id')
            ->get();

        return [
            'response' => [
                'data' => $managers,
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page)
            ],
            'status_code' => 200
        ];
    }
}
