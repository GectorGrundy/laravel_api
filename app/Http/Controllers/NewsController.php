<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\News as R;
use App\Models\News;
use Illuminate\Database\Eloquent\Builder;

class NewsController extends Controller
{
    use Helpers;

    /**
     * @api {POST} /news.create news.create
     * @apiGroup News
     * @apiPermission admin
     *
     * @apiUse news_types
     * @apiParam {String} title
     * @apiParam {String} body
     * @apiParam {Number} offer_id
     * @apiParam {String} published_at Format:<code>Y-m-d H:i:s</code>
     *
     * @apiSampleRequest /news.create
     */
    public function create(R\CreateRequest $request)
    {
        $offer_id = $request->filled('offer_id') ? $request->input('offer_id') : 0;

        $news = News::create(array_merge($request->all(), [
            'author_id' => \Auth::user()->id,
            'offer_id' => $offer_id,
        ]));

        User::incrementUnreadNewsCounter();

        return $this->response->accepted(null, [
            'message' => trans('news.on_create_success'),
            'response' => $news,
            'status_code' => 202
        ]);
    }

    /**
     * @api {POST} /news.edit news.edit
     * @apiGroup News
     * @apiPermission admin
     *
     * @apiUse news_types
     * @apiParam {Number} id
     * @apiParam {String} title
     * @apiParam {String} body
     * @apiParam {Number} offer_id
     * @apiParam {String} published_at Format:<code>Y-m-d H:i:s</code>
     *
     * @apiSampleRequest /news.edit
     */
    public function edit(R\EditRequest $request)
    {
        $news = News::find($request->input('id'))->update($request->all());

        return $this->response->accepted(null, [
            'message' => trans('news.on_edit_success'),
            'response' => $news,
            'status_code' => 202
        ]);
    }

    /**
     * @api {GET} /news.getByHash news.getByHash
     * @apiGroup News
     * @apiPermission admin
     * @apiPermission publisher
     * @apiPermission advertiser
     *
     * @apiParam {String} hash
     * @apiParam {String=offer} [with[]]
     *
     * @apiSampleRequest /news.getByHash
     */
    public function getByHash(R\GetByHashRequest $request)
    {
        $news = News::with($request->get('with', []))
            ->where('hash', $request->input('hash'))
            ->first();
        if (\is_null($news)) {
            $this->response->errorNotFound(trans('news.on_get_error'));
            return;
        }

        return ['response' => $news, 'status_code' => 200];
    }

    /**
     * @api {GET} /news.getList news.getList
     * @apiGroup News
     * @apiPermission admin
     * @apiPermission publisher
     * @apiPermission advertiser
     *
     * @apiParam {String} [date_from='7 days ago'] Format: `Y-m-d`
     * @apiParam {String} [date_to='today'] Format: `Y-m-d`
     * @apiParam {String[]} [offer_hashes[]] To get news without offer send offer_hashes[]=0
     * @apiParam {Number{..100}} [per_page=25]
     * @apiParam {Number} [page=1]
     * @apiParam {Number=0,1} [only_my]
     * 0-all news;<br>
     * 1-show news which belongs to offers which have created flows by publisher
     * @apiParam {String=offer} [with[]]
     *
     * @apiSampleRequest /news.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 25);
        $only_my = (bool)$request->input('only_my', false);
        $offset = paginationOffset($page, $per_page);
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');

        $query = News::whereOffers($request->get('offer_hashes', []))
            ->onlyMy($only_my)
            ->when($date_from, function (Builder $builder) use ($date_from) {
                $builder->createdFrom($date_from . ' 00:00:00');
            })
            ->when($date_to, function (Builder $builder) use ($date_to) {
                $builder->createdTo($date_to . ' 23:59:59');
            });

        $total = clone $query;

        $news = $query
            ->with($request->get('with', []))
            ->offset($offset)
            ->limit($per_page)
            ->latest('id')
            ->get();

        $news_count = $total->count();

        return [
            'response' => [
                'data' => $news,
                'all_loaded' => allEntitiesLoaded($news_count, $page, $per_page),
            ],
            'status_code' => 200
        ];
    }

    /**
     * @api {POST} /news.read news.read
     * @apiGroup News
     * @apiPermission publisher
     * @apiPermission advertiser
     * @apiSampleRequest /news.read
     */
    public function read()
    {
        User::resetUnreadNewsCounter(\Auth::user());

        return $this->response->accepted(null, [
            'status_code' => 202
        ]);
    }

    /**
     * @api {DELETE} /news.delete news.delete
     * @apiGroup News
     * @apiPermission admin
     * @apiParam {Number} id
     * @apiSampleRequest /news.delete
     */
    public function delete(R\DeleteRequest $request)
    {
        News::find($request->input('id'))->delete();

        return $this->response->accepted(null, [
            'message' => trans('news.on_delete_success'),
            'status_code' => 202
        ]);
    }
}
