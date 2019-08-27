<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use DB;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Advertiser as R;
use App\Models\{
    Account, Advertiser, AdvertiserProfile, Currency
};
use Illuminate\Database\Eloquent\Builder;

class AdvertisersController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /advertiser.changeProfile advertiser.changeProfile
     * @apiGroup Advertiser
     * @apiPermission advertiser
     * @apiPermission admin
     * @apiUse timezone
     * @apiParam {Number} [user_id] Advertiser ID. Required for admin
     * @apiParam {Number} [manager_id] Advertiser's managera ID. Required for admin
     * @apiParam {String{..255}} [info] Required for admin
     * @apiParam {String{..255}} full_name
     * @apiParam {String{..255}} skype
     * @apiParam {String{..255}} telegram
     * @apiParam {String{..16}} phone
     * @apiParam {String{..16}} whatsapp
     * @apiParam {String=ru,en} interface_locale
     * @apiSampleRequest /advertiser.changeProfile
     */
    public function changeProfile(R\ChangeProfileRequest $request)
    {
        $user = Auth::user();
        $user_id = $user['id'];
        $inputs = $request->only(['full_name', 'skype', 'telegram', 'phone', 'whatsapp']);

        if ($user->isAdmin()) {
            $user_id = \Hashids::decode($request->input('user_hash'))[0];
            $inputs['manager_id'] = $request->get('manager_id', 0);
            $inputs['info'] = $request->get('info');

        } elseif ($user->isManager()) {
            $user_id = \Hashids::decode($request->input('user_hash'))[0];

        } elseif ($user->isAdvertiser()) {
            $user->updateTimezone($request->input('timezone'));

            if ($request->filled('interface_locale')) {
                $user->update([
                    'locale' => $request->input('interface_locale')
                ]);
            }
        }

        $profile = AdvertiserProfile::where('user_id', $user_id)->firstOrFail();
        $profile->update($inputs);

        $user = Advertiser::with(['profile'])->find($user_id);

        return [
            'message' => trans('users.on_change_profile_success'),
            'response' => $user,
            'status_code' => 202
        ];
    }

    /**
     * @api {GET} /advertiser.getList advertiser.getList
     * @apiGroup Advertiser
     * @apiPermission admin
     * @apiParam {Number} page
     * @apiParam {Number} per_page Max: `200`
     * @apiParam {String[]=profile,accounts} [with[]]
     * @apiParam {String=email} [search_field]
     * @apiParam {String} [search]
     * @apiParam {String[]} [hashes[]] Get advertisers by this hashes
     *
     * @apiSampleRequest /advertiser.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page');
        $per_page = (int)$request->input('per_page');
        $offset = paginationOffset($page, $per_page);

        $query = Advertiser::search($request->input('search_field'), $request->input('search'))
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
     * @api {GET} /advertiser.getSummary advertiser.getSummary
     * @apiGroup Advertiser
     * @apiPermission admin
     * @apiSampleRequest /advertiser.getSummary
     */
    public function getSummary()
    {
        $summary = Account::select(
                DB::raw('SUM(CASE WHEN `currency_id` = ' .
                    Currency::USD_ID . ' THEN `balance` ELSE 0 END) as balance_usd'),

                DB::raw('SUM(CASE WHEN `currency_id` = ' .
                    Currency::RUB_ID . ' THEN `balance` ELSE 0 END) as balance_rub'),

                DB::raw('SUM(CASE WHEN `currency_id` = ' .
                    Currency::EUR_ID . ' THEN `balance` ELSE 0 END) as balance_eur'),
                'currency_id'
            )
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

    public function getWithUncompletedLeads(R\GetWithUncompletedLeadsRequest $request)
    {
        return [
            'response' => Advertiser::with($request->input('with', []))
                ->whereHas('profile', function (Builder $builder) {
                    return $builder->hasUncompletedLeads();
                })->get(),
            'status_code' => 200
        ];
    }

    /**
     * @api {GET} /advertiser.getByHash advertiser.getByHash
     * @apiGroup User
     * @apiPermission admin
     *
     * @apiParam {String} hash
     * @apiParam {String[]=accounts,profile} hash
     * @apiSampleRequest /advertiser.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $advertiser_id = \Hashids::decode($request->input('hash'))[0] ?? false;
        if (!$advertiser_id) {
            $this->response->errorNotFound(trans('users.on_get_error'));
            return;
        }

        $advertiser = Advertiser::with($request->input('with', []))->find($advertiser_id);
        if (\is_null($advertiser)) {
            $this->response->errorNotFound(trans('users.on_get_error'));
            return;
        }

        return [
            'response' => $advertiser,
            'status_code' => 200
        ];
    }
}
