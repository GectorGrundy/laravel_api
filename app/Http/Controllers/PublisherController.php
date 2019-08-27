<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Publisher as R;
use App\Models\{
    Publisher, DataStat, PublisherProfile, User
};

class PublisherController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /publisher.changeProfile publisher.changeProfile
     * @apiGroup Publisher
     * @apiPermission publisher
     * @apiPermission admin
     * @apiPermission support
     * @apiUse timezone
     * @apiParam {String{..255}} full_name
     * @apiParam {String{..255}} skype
     * @apiParam {String{..255}} telegram
     * @apiParam {String=data,utm} [data_type] Required for publisher.
     * @apiParam {String{..16}} phone
     * @apiParam {String} [user_hash] Publisher hash. Required for admin and support
     * @apiParam {Number} [support_id=0] Editable only by admin.
     * @apiParam {String{..255}} [comment] Editable only by admin.
     * @apiParam {Number} [group_id=0] ID of user group. Editable only by admin.
     * @apiSampleRequest /publisher.changeProfile
     */
    public function changeProfile(R\ChangeProfileRequest $request)
    {
        $user = Auth::user();
        $user_id = Auth::user()['id'];
        $inputs = $request->only(['full_name', 'skype', 'telegram', 'phone', 'data_type']);

        if ($user->isAdmin()) {
            $user_id = \Hashids::decode($request->input('user_hash'))[0];
            $inputs['comment'] = $request->input('comment');
            $inputs['support_id'] = $request->input('support_id', 0);

            $group_id = $request->input('group_id', 0);
            User::find($user_id)->update(['group_id' => $group_id]);

        } elseif ($user->isSupport()) {
            $user_id = \Hashids::decode($request->input('user_hash'))[0];

        } else if ($user->isPublisher()) {
            $user->updateTimezone($request->input('timezone'));
        }

        $profile = PublisherProfile::where('user_id', $user_id)->firstOrFail();
        $profile->update($inputs);

        return [
            'message' => trans('users.on_change_profile_success'),
            'status_code' => 202
        ];
    }

    public function getById(R\GetByIdRequest $request)
    {
        $publisher = Publisher::where('id', $request->input('publisher_id'))
            ->with(['offers'])
            ->first();

        return ['response' => $publisher, 'status_code' => 200];
    }

    /**
     * @api {GET} /publisher.getList publisher.getList
     * @apiGroup Publisher
     * @apiPermission admin
     *
     * @apiParam {String=id,hash,email,phone,skype,telegram,balance,hold} [search_field]
     * @apiParam {String} [search]
     * @apiParam {Numeric[]=1,3,5} [currency_id] Required if search_field is `balance` or `hold`
     * @apiParam {Numeric[]=less,more} [constraint] Required if search_field is `balance` or `hold`
     * @apiParam {String[]=profile,group} [with[]]
     * @apiParam {String[]} [hashes[]] Get publishers by these hashes
     *
     * @apiParam {String=email,created_at,balance_rub,balance_usd,balance_eur} [sort_by]
     * @apiParam {String=asc,desc} [sorting] Required if sort_by is set
     *
     * @apiParam {Number[]} [group_ids] Find publishers by these group ids.
     * @apiParam {String=locked,active} [status]
     *
     * @apiParam {Number} [page=1]
     * @apiParam {Number} [per_page=50] Max: `200`
     *
     * @apiSampleRequest /publisher.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 50);
        $offset = paginationOffset($page, $per_page);

        $query = Publisher::search($request->input('search_field'), $request->input('search'))
            ->when($request->input('hashes'), function (Builder $builder) use ($request) {
                $builder->whereIn('hash', $request->input('hashes', []));
            })
            ->when($request->input('group_ids'), function (Builder $builder) use ($request) {
                $builder->whereIn('group_id', $request->input('group_ids', []));
            })
            ->when($request->input('status'), function (Builder $builder) use ($request) {
                $builder->where('status', $request->input('status', []));
            });

        $total = clone $query;
        $total = (int)($total->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        /**
         * @var Collection $publishers
         */
        $publishers = $query
            ->select('users.*')
            ->leftJoin('publisher_profiles as pp', 'users.id', '=', 'pp.user_id')
            ->with($request->input('with', []))
            ->offset($offset)
            ->limit($per_page)
            ->orderBy($request->input('sort_by', 'id'), $request->input('sorting', 'desc'))
            ->get();

        return [
            'response' => [
                'data' => $publishers,
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page)
            ],
            'status_code' => 200
        ];
    }

    /**
     * Возврат списка source паблишера для статистики
     *
     * @param R\GetSourceListRequest $request
     * @return array
     */
    public function getSourceList(R\GetSourceListRequest $request)
    {
        $source_list = DataStat::select('title')
            ->where('publisher_id', Auth::user()['id'])
            ->where('type', $request->get('type'));

        if ($request->get('search', '') !== '') {
            $source_list->where('title', 'like', $request->get('search') . '%');
        } else {
            $source_list->take(10);
        }

        $source_list = $source_list->get();

        return [
            'response' => $source_list,
            'status_code' => 200
        ];
    }

    /**
     * Generate api key for publisher
     *
     * @param R\GenerateApiKeyRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function genApiKey(R\GenerateApiKeyRequest $request)
    {
        $api_key = Str::random();

        PublisherProfile::where('user_id', Auth::user()->id)->update([
            'api_key' => $api_key
        ]);

        return $this->response->accepted(null, [
            'message' => trans('publishers.on_generate_api_key_success'),
            'response' => [
                'api_key' => $api_key
            ],
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /publisher.getSummary publisher.getSummary
     * @apiGroup Publisher
     * @apiPermission admin
     * @apiParam {String=id,hash,email,phone,skype,telegram,balance,hold} [search_field]
     * @apiParam {String} [search]
     * @apiParam {Numeric[]=1,3,5} [currency_id] Required if search_field is `balance` or `hold`
     * @apiParam {Numeric[]=less,more} [constraint] Required if search_field is `balance` or `hold`
     *
     * @apiParam {Number[]} [group_ids] Find publishers by these group ids.
     * @apiParam {String=locked,active} [status]
     *
     * @apiSampleRequest /publisher.getSummary
     */
    public function getSummary(R\GetSummaryRequest $request)
    {
        $summary = Publisher::select(
                DB::raw('SUM(`balance_rub`) as `balance_rub`'),
                DB::raw('SUM(`balance_usd`) as `balance_usd`'),
                DB::raw('SUM(`balance_eur`) as `balance_eur`')
            )
                ->search($request->input('search_field'), $request->input('search'))
                ->when($request->input('group_ids'), function (Builder $builder) use ($request) {
                    $builder->whereIn('group_id', $request->input('group_ids', []));
                })
                ->when($request->input('status'), function (Builder $builder) use ($request) {
                    $builder->where('status', $request->input('status', []));
                })
                ->leftJoin('publisher_profiles', 'publisher_profiles.user_id', '=', 'users.id')
                ->first() ?? [];

        return [
            'response' => [
                'balance_rub' => (float)($summary['balance_rub'] ?? 0),
                'balance_usd' => (float)($summary['balance_usd'] ?? 0),
                'balance_eur' => (float)($summary['balance_eur'] ?? 0),
            ],
            'status_code' => 200
        ];
    }
}
