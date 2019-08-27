<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\PublisherTargetGeo;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\PublisherTargetGeo as R;
use Illuminate\Database\QueryException;

class PublisherTargetGeoController extends Controller
{
    use Helpers;

    public const RELATIONS = [
        'target_geo.offer', 'target_geo.country', 'target_geo.target', 'publisher',
        'target_geo.price_currency', 'target_geo.payout_currency'
    ];

    public function getList(R\GetListRequest $request)
    {
        $target_geo_list = PublisherTargetGeo::with(self::RELATIONS)
            ->offerIds($request->input('offer_ids', []))
            ->publisherIds($request->input('publisher_ids', []))
            ->get();

        return [
            'response' => $target_geo_list,
            'status_code' => 200
        ];
    }

    public function create(R\CreateRequest $request)
    {
        $offer = Offer::with(['targets.target_geo'])->find($request->input('offer_id'));

        $target_geo_list = [];
        $message = trans('publisher_target_geo.on_create_success');

        foreach ($offer->targets as $target) {
            foreach ($target->target_geo as $target_geo) {
                try {
                    $target_geo_list[] = PublisherTargetGeo::create(array_merge($request->all(), [
                        'target_geo_id' => $target_geo->id
                    ]))
                        ->load(self::RELATIONS);
                } catch (QueryException $e) {
                    // 23000 Duplicate entry (Некоторые ставки могли быть добавлены ранее)
                    if ($e->getCode() === 23000) {
                        $message .= ' ' . trans('publisher_target_geo.some_were_skipped');
                    }
                }
            }
        }

        return [
            'message' => $message,
            'response' => $target_geo_list,
            'status_code' => 202
        ];
    }

    public function edit(R\EditRequest $request)
    {
        $target_geo = PublisherTargetGeo::find($request->input('id'));
        $target_geo->update($request->all());

        return [
            'response' => $target_geo->load(self::RELATIONS),
            'status_code' => 202
        ];
    }

    public function delete(R\DeleteRequest $request)
    {
        $geo = PublisherTargetGeo::find($request->input('id'));

        $geo->delete();

        return [
            'status_code' => 202
        ];
    }
}
