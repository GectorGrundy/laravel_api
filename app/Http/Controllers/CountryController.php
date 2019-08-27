<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\TargetGeo;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Country as R;
use App\Models\Country;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CountryController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /country.getById country.getById
     * @apiGroup Country
     * @apiPermission authorized
     * @apiParam {country_id} page
     * @apiSampleRequest /country.getById
     */
    public function getById(R\GetByIdRequest $request)
    {
        $country = Country::find($request->input('country_id'));

        return ['response' => $country, 'status_code' => 200];
    }

    /**
     * @api {GET} /country.getList country.getList
     * @apiGroup Country
     * @apiPermission authorized
     * @apiParam {String[]=offers_quantity} with[]
     * @apiParam {Number=1} only_my
     * @apiSampleRequest /country.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $country = Country::with($request->get('with', []))
            ->active()
            ->orderBy('countries.id')
            ->get();

        return ['response' => $country, 'status_code' => 200];
    }

    /**
     * @api {GET} /country.getListForOfferFilter country.getListForOfferFilter
     * @apiGroup Country
     * @apiPermission authorized
     * @apiSampleRequest /country.getListForOfferFilter
     */
    public function getListForOfferFilter()
    {
        $user = \Auth::user();
        $with = [
            'targets' => function (HasMany $query) use ($user) {
                $query->active()->availableForUser($user);
            },
            'targets.target_geo' => function (HasMany $query) {
                $query->active();
            },
        ];

        $accessible_offers = Offer::with($with)->availableForUser(\Auth::user())->get();
        $offer_ids = $accessible_offers->pluck('id');
        $offers_country_ids = array_unique(data_get($accessible_offers, '*.targets.*.target_geo.*.country_id'));

        $result = [];
        foreach ($offers_country_ids as $country_id) {

            $offers_count = TargetGeo::where('country_id', $country_id)
                ->whereIn('offer_id', $offer_ids)
                ->groupBy('country_id')
                ->count();

            $country = Country::find($country_id)->toArray();

            $result[] = array_merge($country, [
                'offers_count' => $offers_count,
            ]);
        }

        return [
            'response' => $result,
            'status_code' => 200
        ];
    }
}
