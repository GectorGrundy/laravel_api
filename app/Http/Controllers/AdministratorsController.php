<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Administrator as R;
use App\Models\{
    Administrator, AdministratorProfile
};
use Illuminate\Database\Eloquent\Builder;

class AdministratorsController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /administrators.getList administrators.getList
     * @apiGroup Administrator
     * @apiPermission admin
     * @apiParam {Number} page
     * @apiParam {Number} per_page Max: `200`
     * @apiParam {String[]=profile} [with[]]
     * @apiParam {String=email} [search_field]
     * @apiParam {String} [search]
     * @apiSampleRequest /administrators.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page');
        $per_page = (int)$request->input('per_page');
        $offset = paginationOffset($page, $per_page);

        $query = Administrator::search($request->input('search_field'), $request->input('search'))
            ->when($request->input('hashes'), function (Builder $builder) use ($request) {
                $builder->whereIn('hash', $request->input('hashes', []));
            });

        $total = clone $query;
        $total = (int)($total->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        $advertisers = $query
            ->with($request->input('with', []))
            ->offset($offset)
            ->limit($per_page)
            ->latest('id')
            ->get();

        return [
            'response' => [
                'data' => $advertisers,
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page)
            ],
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /administrator.changeProfile administrator.changeProfile
     * @apiDescription Change profile for administrator user role.
     * @apiGroup User
     * @apiPermission admin
     * @apiUse timezone
     * @apiParam {String} full_name Present
     * @apiParam {String} skype Present
     * @apiParam {String} telegram Present
     * @apiSampleRequest /administrator.changeProfile
     */
    public function changeProfile(R\ChangeProfileRequest $request)
    {
        $user = \Auth::user();

        $profile = AdministratorProfile::where('user_id', $user['id'])->firstOrFail();
        $profile->update($request->only(['full_name', 'skype', 'telegram']));

        $user->updateTimezone($request->input('timezone'));

        return [
            'message' => trans('users.on_change_profile_success'),
            'status_code' => 202
        ];
    }
}
