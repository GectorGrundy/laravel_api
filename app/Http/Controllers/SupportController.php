<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use App\Events\User\UserCreated;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Support as R;
use App\Models\{
    Support, SupportProfile, User
};

class SupportController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /support.create support.create
     * @apiGroup Support
     * @apiPermission admin
     * @apiParam {String} email Unique email for user.
     * @apiParam {String} password
     * @apiSampleRequest /support.create
     */
    public function create(R\CreateRequest $request)
    {
        $user = User::create([
            'role' => User::SUPPORT,
            'nickname' => User::generateNickname($request->get('email')),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);

        $user->support()->save(new SupportProfile([]));

        $user = $user->fresh();
        $user->load('support');

        event(new UserCreated($user));

        return [
            'message' => trans('users.on_create_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {POST} /support.changeProfile support.changeProfile
     * @apiGroup Support
     * @apiPermission support
     * @apiPermission admin
     * @apiParam {Number} [user_id] Support ID. Required for admin
     * @apiParam {String{..255}} full_name
     * @apiParam {String{..255}} skype
     * @apiParam {String{..255}} telegram
     * @apiParam {String{..16}} phone
     * @apiSampleRequest /support.changeProfile
     */
    public function changeProfile(R\ChangeProfileRequest $request)
    {
        $user = Auth::user();
        $user_id = Auth::user()['id'];
        $inputs = $request->only(['full_name', 'skype', 'telegram', 'phone']);

        if ($user->isAdmin()) {
            $user_id = $request->input('user_id');
        }

        $profile = SupportProfile::where('user_id', $user_id)->firstOrFail();
        $profile->update($inputs);

        return [
            'message' => trans('users.on_change_profile_success'),
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /support.getList support.getList
     * @apiGroup Support
     * @apiPermission admin
     *
     * @apiParam {Number} [page=1]
     * @apiParam {Number{..100}} [per_page=25]
     * @apiParam {String[]=profile} [with[]]
     *
     * @apiSampleRequest /support.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 25);
        $offset = paginationOffset($page, $per_page);

        $query = Support::search($request->input('search_field'), $request->input('search'));

        $total_leads = clone $query;
        $total = (int)($total_leads->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        $supports = $query
            ->with($request->input('with', []))
            ->offset($offset)
            ->limit($per_page)
            ->latest('id')
            ->get();

        return [
            'response' => [
                'data' => $supports,
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page)
            ],
            'status_code' => 200
        ];
    }
}
