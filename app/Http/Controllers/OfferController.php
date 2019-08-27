<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Auth;
use Hashids;
use App\Exceptions\User\UnknownUserRole;
use App\Strategies\OfferListing\AdminOfferList;
use App\Strategies\OfferListing\AdvertiserOfferList;
use App\Strategies\OfferListing\PublisherOfferList;
use App\Jobs\MoveStaticFile;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\Offer as R;
use App\Events\Offer\OfferEdited;
use App\Events\Offer\OfferCreated;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{
    MyOffer, Offer, OfferRequisiteTranslation, OfferTranslation, Target, TargetGeo, TargetGeoRule, User
};
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfferController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /offer.create offer.create
     * @apiGroup Offer
     * @apiPermission admin
     * @apiUse payout_currency_id
     * @apiUse is_private
     * @apiParam {String{..512}} title
     * @apiParam {String{..512}} translations[]title
     * @apiParam {String{..512}} translations[]agreement
     * @apiParam {String{..512}} translations[]description
     * @apiParam {String{..512}} agreement
     * @apiParam {String{..512}} description
     * @apiParam {String{..255}} thumb_path
     * @apiParam {String} [url]
     * @apiParamExample {json} Request-Example:
     * {"title":"Chocolite","url":"http://google.com","type":"CPA","agreement":"Правила",
     * "description":"Описание","is_private":1,
     * "thumb_path":"/var/www/backend.affninja/storage/app/temp/zMp1GpT5KZ1dGU6x.png",
     * "translations":[{"locale_id":2,"title":"Chocolite EN","description":"Описание EN","agreement":"Правила EN"}]}
     */
    public function create(R\CreateRequest $request)
    {
        $offer = Offer::create(array_merge($request->except(['status']), [
            'status' => Offer::INACTIVE,
        ]));

        $offer->syncTranslations($request->input('translations'));

        $this->dispatch(new MoveStaticFile(
            $request->get('thumb_path'),
            public_path(substr($offer->getThumbPath(), 1))
        ));

        event(new OfferCreated($offer));

        return $this->response->accepted(null, [
            'message' => trans('offers.on_create_success'),
            'response' => $offer,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /offer.edit offer.edit
     * @apiGroup Offer
     * @apiPermission admin
     * @apiUse payout_currency_id
     * @apiUse is_private
     * @apiUse is_active
     * @apiParam {Number} id
     * @apiParam {String{..512}} title
     * @apiParam {String{..512}} title_en
     * @apiParam {String{..512}} agreement
     * @apiParam {String{..512}} agreement_en
     * @apiParam {String{..512}} description
     * @apiParam {String{..512}} description_en
     * @apiParam {String{..255}} thumb_path
     * @apiParam {String=inactive,active,archived} status
     * @apiParam {String} [url]
     * @apiSampleRequest /offer.edit
     */
    public function edit(R\EditRequest $request)
    {
        $offer = Offer::find($request->input('id'));

        $offer->update($request->all());

        $offer->syncTranslations($request->input('translations'));

        if (!$request->get('is_private')) {
            $offer->publishers()->sync([]);
            $offer->user_groups()->sync([]);
        }

        if ($request->filled('thumb_path')) {
            $this->dispatch(new MoveStaticFile(
                $request->get('thumb_path'),
                public_path(substr($offer->getThumbPath(), 1))
            ));
        }

        event(new OfferEdited($offer));

        return $this->response->accepted(null, [
            'message' => trans('offers.on_edit_success'),
            'response' => $offer->load(['translations']),
            'status_code' => 202
        ]);
    }

    public function delete(R\DeleteRequest $request)
    {
        Offer::where('id', $request->id)->update([
            'status' => Offer::ARCHIVED,
        ]);

        return $this->response->accepted(null, [
            'message' => trans('offers.on_delete_success'),
            'status_code' => 202
        ]);
    }

    public function getByHash(R\GetByHashRequest $request)
    {
        $offer_id = Hashids::decode($request->input('offer_hash'))[0];
        $user = Auth::user();

        $offer = Offer::with($request->input('with', []))->where('offers.id', $offer_id);

        switch ($request->get('for')) {
            case 'offer_profile':
                switch ($user['role']) {
                    case User::ADMINISTRATOR:
                        $offer = $offer->with([
                            'translations',
                            'targets.locale',
                            'targets.template',
                            'targets.target_geo.country',
                            'targets.target_geo.payout_currency',
                            'targets.target_geo.price_currency', 'offer_sources',
                            'targets.target_geo.fallback_target_geo_rule',
                            'publishers',
                            'user_groups',
                            'targets.target_geo.target_geo_rules' => function ($q) {
                                $q->orderBy('priority', 'desc');
                            },
                            'targets.target_geo.target_geo_rules.integration',
                            'targets.target_geo.target_geo_rules.advertiser',
                            'targets.target_geo.integration.advertiser',
                            'targets.publishers',
                            'targets.user_groups',
                            'targets.target_geo.integration',
                            'offer_categories',
                            'labels', 'email_integration', 'sms_integration',
                            'offer_requisites', 'offer_requisites.locale', 'offer_categories',
                            'targets.landings.locale', 'targets.landings.domains', 'targets.landings.publishers',
                            'targets.transits.locale', 'targets.transits.domains', 'targets.transits.publishers',
                        ])
                            ->first()
                            ->toArray();
                        break;

                    case User::PUBLISHER:
                        $offer = $offer
                            ->with([
                                'targets' => function (HasMany $query) use ($user) {
                                    $query->active()->availableForUser($user);
                                },
                                'targets.target_geo' => function (HasMany $query) {
                                    $query->active();
                                },
                                'targets.locale',
                                'targets.template',
                                'targets.landings' => function (HasMany $query) {
                                    $query->userHaveAccess();
                                },
                                'targets.transits' => function (HasMany $query) {
                                    $query->userHaveAccess();
                                },
                                'targets.landings.locale', 'targets.landings.domains',
                                'targets.transits.locale', 'targets.transits.domains',
                                'targets.target_geo.country',
                                'targets.target_geo.payout_currency', 'targets.target_geo.price_currency',
                                'offer_sources', 'offer_categories', 'labels',
                            ])
                            ->first();

                        if (\is_null($offer)) {
                            $this->response->errorNotFound(trans('offers.on_get_error'));
                            return;
                        }

                        $target_geo = new TargetGeo();
                        foreach ($offer->targets as &$target) {
                            $target->target_geo = $target_geo->replaceCustomStakes(
                                $target->target_geo, $user->id
                            );
                        }
                        break;

                    case User::ADVERTISER:
                        $offer_with = [
                            'offer_sources',
                            'offer_categories',
                            'labels',
                            'targets' => function (HasMany $builder) {
                                $builder->whereHas('target_geo.rules');
                            },
                            'targets.landings' => function (HasMany $query) {
                                $query->userHaveAccess();
                            },
                            'targets.transits' => function (HasMany $query) {
                                $query->userHaveAccess();
                            },
                            'targets.locale',
                            'targets.template',
                            'targets.landings.locale',
                            'targets.landings.domains',
                            'targets.transits.locale',
                            'targets.transits.domains',
                            'targets.target_geo' => function (HasMany $builder) {
                                $builder->whereHas('rules');
                            },
                            'targets.target_geo.rules',
                            'targets.target_geo.country',
                            'targets.target_geo.payout_currency',
                            'targets.target_geo.price_currency',
                        ];

                        $offer = $offer->with($offer_with)->active()->first();
                        break;
                }
                break;

            default:
                if ($user->isAdmin()) {
                    $offer = $offer->first();
                } else {
                    $offer = $offer->active()->first();
                }
                break;
        }

        if (\is_null($offer)) {
            $this->response->errorNotFound(trans('offers.on_get_error'));
            return;
        }

        if ($user->isPublisher() && $offer->isInactiveForPublisher()) {
            $this->response->errorNotFound(trans('offers.on_get_error'));
            return;
        }

        return ['response' => $offer, 'status_code' => 200];
    }

    public function getList(R\GetListRequest $request)
    {
        $user = Auth::user();

        /**
         * @var Builder $offers
         */
        $offers = Offer::onlyMy($user, (int)$request->input('only_my'))
            ->categories($request->get('category_ids', []))
            ->sources($request->get('source_ids', []))
            ->countries($request->get('country_ids', []))
            ->whereLabels($request->get('labels', []))
            ->search($request->get('search'))
            ->latest('offers.id');

        switch ($request->get('for')) {
            case 'offer_list':
                switch ($user['role']) {
                    case User::ADMINISTRATOR:
                        $offers = (new AdminOfferList())->get($request, $offers);
                        break;

                    case User::ADVERTISER:
                        $offers = (new AdvertiserOfferList())->get($request, $offers);
                        break;

                    case User::PUBLISHER:
                        $offers = (new PublisherOfferList())->get($request, $offers);
                        break;

                    default:
                        throw new UnknownUserRole($user['role'], ' for offer list');
                }
                break;

            default:
                $offers = $offers->availableForUser($user)->get();
                break;
        }

        return ['response' => $offers, 'status_code' => 200];
    }

    /**
     * Привязка категории к офферу
     *
     * @param R\SyncCategoriesRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function syncCategories(R\SyncCategoriesRequest $request)
    {
        $offer = Offer::find($request->get('id'));

        $clone_for_sync = clone $offer;
        $clone_for_sync->offer_categories()->sync($request->get('categories', []));

        $offer_categories = $offer->load(['offer_categories'])['offer_categories'];

        return $this->response->accepted(null, [
            'message' => trans('offers.on_sync_categories_success'),
            'response' => $offer_categories,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /offer.syncLabels offer.syncLabels
     * @apiGroup Offer
     * @apiPermission admin
     * * @apiParamExample {json} Request-Example:
     * {"id":"1","labels":[{"label_id":"1","available_at":""},{"label_id":"2","available_at":"2018-01-01 12:00:12"}]}
     * @apiSampleRequest /offer.edit
     */
    public function syncLabels(R\SyncLabelsRequest $request)
    {
        $offer = Offer::find($request->input('id'));

        $labels = [];
        foreach ($request->input('labels', []) as $item) {
            $labels[$item['label_id']]['available_at'] = (empty($item['available_at'])) ? null : $item['available_at'];
        }

        $offer->labels()->sync($labels);

        $offer_labels = $offer->load(['labels'])->labels;

        return $this->response->accepted(null, [
            'message' => trans('offers.on_sync_labels_success'),
            'response' => $offer_labels,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /offer.syncPublishers offer.syncPublishers
     * @apiDescription Set permissions by user groups.
     * To forbid access for all publishers, do not send publishers[] param.
     * @apiGroup Offer
     * @apiPermission admin
     * @apiParam {Number} offer_id
     * @apiParam {Object[]} [publishers][]
     * @apiParamExample {json} Request-Example:
     * { "offer_id": 1, "publishers": [
     *  {"publisher_id": 1}, {"publisher_id": 2}
     * ]}
     */
    public function syncPublishers(R\SyncPublishersRequest $request)
    {
        $offer = Offer::find($request->get('offer_id'));

        $clone_for_sync = clone $offer;
        $clone_for_sync->publishers()->sync($request->get('publishers', []));

        $publishers = $offer->load(['publishers'])['publishers'];

        return $this->response->accepted(null, [
            'message' => trans('offers.on_change_privacy_success'),
            'response' => $publishers,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /offer.syncUserGroups offer.syncUserGroups
     * @apiDescription Set permissions by user groups.
     * To forbid access for all groups, do not send user_groups[] param.
     * @apiGroup Offer
     * @apiPermission admin
     * @apiParam {Number} offer_id
     * @apiParam {Object[]} [user_groups][]
     * @apiParamExample {json} Request-Example:
     * { "offer_id": 1, "user_groups": [
     *  {"user_group_id": 1}, {"user_group_id": 2}
     * ]}
     */
    public function syncUserGroups(R\SyncUserGroupsRequest $request)
    {
        $offer = Offer::find($request->get('offer_id'));

        $offer->user_groups()->sync(
            collect($request->get('user_groups', []))->keyBy('user_group_id')->toArray()
        );

        $user_groups = $offer->load(['user_groups'])['user_groups'];

        return $this->response->accepted(null, [
            'message' => trans('offers.on_change_privacy_success'),
            'response' => $user_groups,
            'status_code' => 202
        ]);
    }

    /**
     * Обновление списка разрешенных источников трафика оффера
     *
     * @param R\SyncOfferSourcesRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function syncOfferSources(R\SyncOfferSourcesRequest $request)
    {
        $offer = Offer::find($request->get('id'));

        $clone_for_sync = clone $offer;
        $clone_for_sync->offer_sources()->sync($request->get('offer_sources', []));

        $offer_sources = $offer->load(['offer_sources'])['offer_sources'];

        return $this->response->accepted(null, [
            'message' => trans('offers.on_sync_offer_sources_success'),
            'response' => $offer_sources,
            'status_code' => 202
        ]);
    }

    /**
     * Добавление оффера в список офферов паблишера
     *
     * @param R\AddToMyRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function addToMy(R\AddToMyRequest $request)
    {
        $offer_id = (int)Hashids::decode($request->get('offer_hash'))[0];

        MyOffer::createNew($offer_id, Auth::user()->id);

        return $this->response->accepted(null, [
            'message' => trans('offers.on_add_to_my_success'),
            'response' => [],
            'status_code' => 202
        ]);
    }

    /**
     * Удаление оффера со списока офферов паблишера
     *
     * @param R\RemoveFromMyRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function removeFromMy(R\RemoveFromMyRequest $request)
    {
        $offer_id = Hashids::decode($request->get('offer_hash'))[0];

        MyOffer::where('publisher_id', Auth::id())->where('offer_id', $offer_id)->delete();

        return $this->response->accepted(null, [
            'message' => trans('offers.on_remove_from_my_success'),
            'response' => [],
            'status_code' => 202
        ]);
    }

    public function clone(R\CloneRequest $request)
    {
        /**
         * @var Offer $cloning_offer
         */
        $cloning_offer = Offer::find($request->id);
        /**
         * @var Offer $offer
         */
        $offer = $cloning_offer->replicate([
            'status',
        ]);

        $offer->title = $request->title;
        $offer->save();

        $this->dispatch(new MoveStaticFile(
            $request->get('thumb_path'),
            public_path(substr($offer->getThumbPath(), 1))
        ));

        // Email integration
        if (!empty($cloning_offer->email_integration)) {
            $email_integration = $cloning_offer->email_integration->replicate();
            $email_integration->offer_id = $offer->id;
            $email_integration->save();
        }

        foreach ($cloning_offer->offer_requisites as $requisite) {
            $requisite = $requisite->toArray();

            OfferRequisiteTranslation::create(array_merge($requisite, [
                'offer_id' => $offer['id']
            ]));
        }

        foreach ($cloning_offer->translations as $translation) {
            $translation = $translation->toArray();

            OfferTranslation::create(array_merge($translation, [
                'offer_id' => $offer['id']
            ]));
        }

        // Источники трафика
        $source_ids = collect($cloning_offer->offer_sources->toArray())->pluck('id')->toArray();
        $offer->offer_sources()->sync($source_ids);

        foreach ($cloning_offer->targets as $target) {
            $target_fields = $target->toArray();

            // Цели
            $new_target = Target::create(array_merge($target_fields, [
                'offer_id' => $offer['id']
            ]));

            // Гео цели
            foreach ($target->target_geo as $target_geo) {

                $target_geo_fields = $target_geo->toArray();

                $new_target_geo = TargetGeo::create(array_merge($target_geo_fields, [
                    'target_id' => $new_target['id'],
                    'offer_id' => $offer['id'],
                ]));

                // Правила
                foreach ($target_geo->target_geo_rules as $target_geo_rule) {
                    $target_geo_rule_fields = $target_geo_rule->toArray();

                    TargetGeoRule::create(array_merge($target_geo_rule_fields, [
                        'target_geo_id' => $new_target_geo['id'],
                    ]));
                }
            }
        }

        return $this->response->accepted(null, [
            'message' => trans('offers.on_clone_success'),
            'response' => $offer,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /offer.getCountByLabels offer.getCountByLabels
     * @apiGroup Deposit
     * @apiPermission publisher
     * @apiParam {Nember[]} labels[] Id's of labels
     * @apiSampleRequest /offer.getCountByLabels
     */
    public function getCountByLabels(R\GetCountByLabelsRequest $request)
    {
        $label_ids = $request->input('labels', []);
        $count = [];

        foreach ($label_ids as $label_id) {
            $offers = Offer::whereLabels([$label_id])->get();
            $offers = Offer::rejectInactiveOffersForPublisher($offers);

            $count[$label_id] = $offers->count();
        }

        return ['response' => $count, 'status_code' => 200];
    }
}
