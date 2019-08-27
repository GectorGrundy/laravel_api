<?php
declare(strict_types=1);

use App\Classes\ElasticSchema;
use Elasticsearch\Client;
use Illuminate\Database\Migrations\Migration;

class AddApiMethodTypeToElasticsearch extends Migration
{
    public function up()
    {

        $client = app(Client::class);

        $settings = [
            'index' => ElasticSchema::INDEX,
            'type' => ElasticSchema::API_LOG_TYPE,
            'body' => [
                ElasticSchema::API_LOG_TYPE => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => [
                        'title' => [],
                    ]
                ],
            ]
        ];
        $client->indices()->putMapping($settings);
    }
}
