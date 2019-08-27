<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\ElasticSchema;
use App\Models\ApiLog;
use Dingo\Api\Routing\Helpers;
use App\Http\Requests\ApiLog as R;
use Elasticsearch\Client;

class ApiLogController extends Controller
{
    use Helpers;

    /**
     * @api {GET} /api_log.getList api_log.getList
     * @apiGroup ApiLog
     * @apiPermission admin
     *
     * @apiParam {String} date_from Date in format:<code>Y-m-d</code>
     * @apiParam {String} date_to Date in format:<code>Y-m-d</code>
     * @apiParam {String[]} [user_hashes[]]
     * @apiParam {String[]} [api_methods[]]
     * @apiParam {Number{..200}} [per_page=50]
     * @apiParam {Number} [page=1]
     *
     * @apiSampleRequest /api_log.getList
     */
    public function getList(R\GetListRequest $request)
    {
        $page = (int)$request->input('page', 1);
        $per_page = (int)$request->input('per_page', 50);
        $offset = paginationOffset($page, $per_page);

        $query = ApiLog::createdBetweenDates($request->input('date_from'), $request->input('date_to'))
            ->whereUser($request->get('user_hashes', []))
            ->whereMethod($request->get('api_methods', []));

        $total = clone $query;
        $total = (int)($total->select(\DB::raw('COUNT(*) as `total`'))->first()['total'] ?? 0);

        $logs = $query->with(['user', 'admin'])->offset($offset)->limit($per_page)->latest('id')->get();

        return [
            'response' => [
                'all_loaded' => allEntitiesLoaded($total, $page, $per_page),
                'data' => $logs,
            ],
            'status_code' => 200
        ];
    }

    /**
     * @api {GET} /api_log.search api_log.search
     * @apiGroup ApiLog
     * @apiPermission admin
     * @apiParam {String} search
     * @apiSampleRequest /api_log.search
     */
    public function search(R\SearchRequest $request)
    {
        $client = app(Client::class);
        $response = $client->search([
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::API_LOG_TYPE,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $request->input('search'),
                        'type' => 'phrase_prefix',
                        'fields' => ['title']
                    ]
                ]
            ]
        ]);

        $titles = [0];
        if (isset($response['hits']['total']) && $response['hits']['total'] > 0) {
            $titles = collect($response['hits']['hits'])->pluck('_source')->transform(function ($title) {
                return $title;
            })->toArray();
        }

        return [
            'response' => $titles,
            'status_code' => 200
        ];
    }
}
