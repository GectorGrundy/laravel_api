<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Classes\ElasticSchema;
use App\Models\ApiLog;
use Dingo\Api\Routing\Router;
use Elasticsearch\Client;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

class ElasticIndexApiMethods extends Command
{
    protected $name = 'api_methods:index';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    public function handle()
    {
        $clear_params = [
            'index' =>  ElasticSchema::INDEX,
            'type' => ElasticSchema::API_LOG_TYPE,
            'body' => [
                'query' => [
                    'match_all' => (object)[]
                ]
            ]
        ];
        $this->client->deleteByQuery($clear_params);

        $logs = \DB::table('api_logs')->select(['api_method'])->groupBy('api_method')->get();

        foreach ($logs as $index => $log) {
            $index_params = [
                'index' => ElasticSchema::INDEX,
                'type' => ElasticSchema::API_LOG_TYPE,
                'id' => $index,
                'body' => [
                    'title' => $log->api_method,
                ]
            ];
            $this->client->index($index_params);
        }
    }
}
